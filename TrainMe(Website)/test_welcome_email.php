<?php
/**
 * Test Welcome Email
 */

require_once 'email_helper.php';

$testEmail = 'eyadsalah710@gmail.com'; // Change to your email
$testName = 'Test User';
$testRole = 'employee';

echo "<h2>Welcome Email Test</h2>";

if (class_exists('TrainMeEmail')) {
    echo "<p style='color: green;'>✓ TrainMeEmail class found</p>";
    
    try {
        $emailHelper = new TrainMeEmail();
        echo "<p style='color: green;'>✓ TrainMeEmail instance created</p>";
        
        echo "<p>Sending welcome email to: <strong>$testEmail</strong></p>";
        echo "<p>Please wait...</p>";
        
        $result = $emailHelper->sendWelcomeEmail($testEmail, $testName, $testRole);
        
        if (is_array($result)) {
            if (isset($result['success']) ? $result['success'] : false) {
                echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✓ Welcome email sent successfully!</p>";
                echo "<p>Check your inbox at <strong>$testEmail</strong></p>";
            } else {
                echo "<p style='color: red; font-weight: bold; font-size: 18px;'>❌ Welcome email failed!</p>";
                echo "<p><strong>Error:</strong> " . (isset($result['message']) ? htmlspecialchars($result['message']) : 'Unknown error') . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Unexpected result format: " . gettype($result) . "</p>";
            var_dump($result);
        }
    } catch (Exception $e) {
        echo "<p style='color: red; font-weight: bold;'>❌ Exception occurred!</p>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Trace:</strong></p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
} else {
    echo "<p style='color: red;'>❌ TrainMeEmail class not found!</p>";
    echo "<p>Make sure email_helper.php is loaded correctly.</p>";
}

echo "<hr>";
echo "<h3>Check PHP Error Logs</h3>";
echo "<p>If the email failed, check your PHP error logs for detailed error messages.</p>";
echo "<p>Error log location: " . ini_get('error_log') . "</p>";
?>

