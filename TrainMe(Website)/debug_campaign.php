<?php
session_start();
require_once 'index.php'; // This will load getDBConnection

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Admin access required');
}

$pdo = getDBConnection();
if ($pdo === null) {
    die('Database connection failed');
}

echo "<h2>Campaign Email Debugging</h2>";

// Get the most recent campaign
$stmt = $pdo->query("SELECT * FROM campaigns ORDER BY created_at DESC LIMIT 1");
$campaign = $stmt->fetch();

if (!$campaign) {
    echo "<p style='color: red;'>No campaigns found in database.</p>";
    exit;
}

echo "<h3>Campaign Details:</h3>";
echo "<pre>";
print_r($campaign);
echo "</pre>";

// Check employees
$stmt = $pdo->query("SELECT id, name, email, role FROM users WHERE role = 'employee'");
$employees = $stmt->fetchAll();

echo "<h3>Employees in Database:</h3>";
if (empty($employees)) {
    echo "<p style='color: red;'>❌ NO EMPLOYEES FOUND! This is why no emails were sent.</p>";
} else {
    echo "<p style='color: green;'>✓ Found " . count($employees) . " employees:</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
    foreach ($employees as $emp) {
        echo "<tr><td>{$emp['id']}</td><td>{$emp['name']}</td><td>{$emp['email']}</td><td>{$emp['role']}</td></tr>";
    }
    echo "</table>";
}

// Check campaign recipients
$stmt = $pdo->prepare("SELECT cr.*, u.name as user_name, u.email as user_email 
                        FROM campaign_recipients cr 
                        LEFT JOIN users u ON cr.user_id = u.id 
                        WHERE cr.campaign_id = ?");
$stmt->execute([$campaign['id']]);
$recipients = $stmt->fetchAll();

echo "<h3>Campaign Recipients (Campaign ID: {$campaign['id']}):</h3>";
if (empty($recipients)) {
    echo "<p style='color: red;'>❌ NO RECIPIENTS FOUND in campaign_recipients table!</p>";
    echo "<p>This means recipients were not added when the campaign was created.</p>";
} else {
    echo "<p style='color: green;'>✓ Found " . count($recipients) . " recipients:</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Name</th><th>Email</th><th>Status</th><th>Sent At</th></tr>";
    foreach ($recipients as $rec) {
        $statusColor = $rec['status'] === 'sent' ? 'green' : ($rec['status'] === 'failed' ? 'red' : 'orange');
        echo "<tr>";
        echo "<td>{$rec['id']}</td>";
        echo "<td>{$rec['user_id']}</td>";
        echo "<td>{$rec['user_name']}</td>";
        echo "<td>{$rec['user_email']}</td>";
        echo "<td style='color: $statusColor;'><strong>{$rec['status']}</strong></td>";
        echo "<td>" . (isset($rec['sent_at']) ? $rec['sent_at'] : 'Not sent') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check if campaign is draft
if ($campaign['status'] === 'draft') {
    echo "<p style='color: orange;'>⚠️ Campaign is a DRAFT. Drafts don't send emails automatically.</p>";
    echo "<p>You need to click 'Send' button in the admin dashboard to send emails.</p>";
} else {
    echo "<p style='color: green;'>✓ Campaign status: {$campaign['status']} (should send emails)</p>";
}

// Test email sending
echo "<h3>Test Email Sending:</h3>";
if (class_exists('TrainMeEmail')) {
    echo "<p style='color: green;'>✓ TrainMeEmail class exists</p>";
    
    // Check SMTP config
    $configFile = __DIR__ . '/smtp_config.php';
    if (file_exists($configFile)) {
        $config = include $configFile;
        if (!empty($config['password'])) {
            echo "<p style='color: green;'>✓ SMTP password is configured</p>";
        } else {
            echo "<p style='color: red;'>❌ SMTP password is empty in config file</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ SMTP config file not found</p>";
    }
    
    // Try to send a test email
    if (isset($_GET['test_email']) && !empty($employees)) {
        $testEmail = $_GET['test_email'];
        echo "<p>Attempting to send test email to: $testEmail</p>";
        
        try {
            $emailHelper = new TrainMeEmail();
            $result = $emailHelper->sendCampaignEmail(
                $testEmail,
                'Test Campaign Email',
                '<h1>Test Email</h1><p>This is a test email from TrainMe campaign system.</p>',
                'Test Sender',
                'test@trainme.com'
            );
            
            if (is_array($result) && (isset($result['success']) ? $result['success'] : false)) {
                echo "<p style='color: green;'>✓ Test email sent successfully!</p>";
            } else {
                $errorMsg = isset($result['message']) ? $result['message'] : 'Unknown error';
                echo "<p style='color: red;'>❌ Test email failed: " . $errorMsg . "</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
        }
    } else {
        if (!empty($employees)) {
            $firstEmployee = $employees[0];
            echo "<p><a href='?test_email={$firstEmployee['email']}'>Send Test Email to {$firstEmployee['email']}</a></p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ TrainMeEmail class not found!</p>";
}

// Check PHP error log location
echo "<h3>Debugging Tips:</h3>";
echo "<ul>";
echo "<li>Check your PHP error logs for detailed error messages</li>";
echo "<li>Error log location: " . ini_get('error_log') . "</li>";
echo "<li>If no employees found, create some employee accounts first</li>";
echo "<li>If recipients not added, check the campaign creation code</li>";
echo "<li>If emails fail to send, check SMTP configuration</li>";
echo "</ul>";
?>

