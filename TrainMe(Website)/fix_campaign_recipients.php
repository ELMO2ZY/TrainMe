<?php
/**
 * Fix Campaign Recipients - Manually add recipients to a campaign
 */

session_start();
require_once 'index.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Admin access required');
}

$pdo = getDBConnection();
if ($pdo === null) {
    die('Database connection failed');
}

echo "<h2>Fix Campaign Recipients</h2>";

// Get latest campaign
$stmt = $pdo->query("SELECT * FROM campaigns ORDER BY created_at DESC LIMIT 1");
$campaign = $stmt->fetch();

if (!$campaign) {
    echo "<p>No campaigns found.</p>";
    exit;
}

$campaignId = $campaign['id'];
echo "<h3>Campaign: {$campaign['name']} (ID: $campaignId)</h3>";

// Get employees
$stmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'employee'");
$employees = $stmt->fetchAll();

echo "<p><strong>Employees found:</strong> " . count($employees) . "</p>";

if (empty($employees)) {
    echo "<p style='color: red;'>No employees found in database!</p>";
    exit;
}

// Check existing recipients
$stmt = $pdo->prepare("SELECT user_id FROM campaign_recipients WHERE campaign_id = ?");
$stmt->execute([$campaignId]);
$existingRecipients = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'user_id');

echo "<p><strong>Existing recipients:</strong> " . count($existingRecipients) . "</p>";

// Add missing recipients
if (isset($_GET['add_recipients'])) {
    $added = 0;
    $errors = 0;
    
    foreach ($employees as $employee) {
        // Skip if already added
        if (in_array($employee['id'], $existingRecipients)) {
            continue;
        }
        
        try {
            $token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("
                INSERT INTO campaign_recipients (campaign_id, user_id, email, status, token)
                VALUES (?, ?, ?, 'pending', ?)
            ");
            $result = $stmt->execute([
                $campaignId,
                $employee['id'],
                $employee['email'],
                $token
            ]);
            
            if ($result) {
                $added++;
                echo "<p style='color: green;'>✓ Added: {$employee['name']} ({$employee['email']})</p>";
            } else {
                $errors++;
                $errorInfo = $stmt->errorInfo();
                echo "<p style='color: red;'>✗ Failed: {$employee['name']} - " . print_r($errorInfo, true) . "</p>";
            }
        } catch (PDOException $e) {
            $errors++;
            echo "<p style='color: red;'>✗ Error adding {$employee['name']}: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<p><strong>Summary:</strong> Added $added, Errors: $errors</p>";
    
    if ($added > 0) {
        // Update campaign recipient count
        try {
            $stmt = $pdo->prepare("UPDATE campaigns SET total_recipients = ? WHERE id = ?");
            $stmt->execute([$added + count($existingRecipients), $campaignId]);
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>Could not update total_recipients: " . $e->getMessage() . "</p>";
        }
        
        // Send emails if campaign is not draft
        if ($campaign['status'] !== 'draft') {
            echo "<h3>Sending Emails...</h3>";
            try {
                $sentCount = sendCampaignEmails($campaignId, $pdo);
                echo "<p style='color: green; font-weight: bold;'>✓ Sent $sentCount emails!</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Error sending emails: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<hr>";
    echo "<p><a href='admin.php'>Go to Admin Dashboard</a></p>";
} else {
    echo "<h3>Employees to Add:</h3>";
    echo "<ul>";
    foreach ($employees as $emp) {
        $exists = in_array($emp['id'], $existingRecipients);
        $status = $exists ? "<span style='color: green;'>✓ Already added</span>" : "<span style='color: orange;'>Missing</span>";
        echo "<li>{$emp['name']} ({$emp['email']}) - $status</li>";
    }
    echo "</ul>";
    
    $missing = count($employees) - count($existingRecipients);
    if ($missing > 0) {
        echo "<p style='color: red; font-weight: bold;'>$missing recipients are missing!</p>";
        echo "<p><a href='?add_recipients=1' style='display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Add Missing Recipients & Send Emails</a></p>";
    } else {
        echo "<p style='color: green;'>✓ All recipients are already added.</p>";
    }
}
?>

