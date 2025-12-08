<?php
/**
 * Test Email Script for TrainMe
 * Run this script to test if email sending is working correctly
 */

// Include email helper
require_once __DIR__ . '/email_helper.php';

// Test email configuration
$testEmail = 'ststicket2525@gmail.com'; // Change this to your email address
$testName = 'Test User';

echo "Testing TrainMe Email System...\n";
echo "================================\n\n";

// Check if PHPMailer is available
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "❌ ERROR: PHPMailer is not installed!\n";
    echo "   Run: composer install\n";
    exit(1);
} else {
    echo "✅ PHPMailer is installed\n";
}

// Check if TrainMeEmail class exists
if (!class_exists('TrainMeEmail')) {
    echo "❌ ERROR: TrainMeEmail class not found!\n";
    exit(1);
} else {
    echo "✅ TrainMeEmail class found\n";
}

// Create email helper instance
try {
    $emailHelper = new TrainMeEmail();
    echo "✅ TrainMeEmail instance created\n";
} catch (Exception $e) {
    echo "❌ ERROR creating TrainMeEmail: " . $e->getMessage() . "\n";
    exit(1);
}

// Test sending welcome email
echo "\nAttempting to send test welcome email to: $testEmail\n";
echo "Please change \$testEmail in this script to your actual email address.\n\n";

try {
    $result = $emailHelper->sendWelcomeEmail($testEmail, $testName, 'employee');
    
    if (is_array($result) && ($result['success'] ?? false)) {
        echo "✅ SUCCESS: Email sent successfully!\n";
        echo "   Message: " . ($result['message'] ?? 'No message') . "\n";
    } else {
        echo "❌ FAILED: Email sending failed\n";
        echo "   Error: " . ($result['message'] ?? 'Unknown error') . "\n";
        echo "\nCommon issues:\n";
        echo "1. Check if Gmail App Password is correct in smtp_config.php\n";
        echo "2. Verify 2-Step Verification is enabled on Gmail account\n";
        echo "3. Generate a new App Password if the current one is expired\n";
        echo "4. Check PHP error logs for detailed SMTP debug information\n";
    }
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n================================\n";
echo "Test completed. Check your email inbox and spam folder.\n";
echo "Also check PHP error logs for detailed SMTP connection information.\n";

