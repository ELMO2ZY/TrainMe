<?php
/**
 * Check Campaign Creation Logs
 * This will show the last campaign and check if recipients were added
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

echo "<h2>Campaign Status Check</h2>";

// Get latest campaign
$stmt = $pdo->query("SELECT * FROM campaigns ORDER BY created_at DESC LIMIT 1");
$campaign = $stmt->fetch();

if (!$campaign) {
    echo "<p>No campaigns found.</p>";
    exit;
}

echo "<h3>Latest Campaign:</h3>";
echo "<p><strong>ID:</strong> {$campaign['id']}</p>";
echo "<p><strong>Name:</strong> {$campaign['name']}</p>";
echo "<p><strong>Status:</strong> {$campaign['status']}</p>";
echo "<p><strong>Total Recipients (from campaigns table):</strong> " . ($campaign['total_recipients'] ?? 0) . "</p>";

// Check actual recipients
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM campaign_recipients WHERE campaign_id = ?");
$stmt->execute([$campaign['id']]);
$actualCount = $stmt->fetch()['count'];

echo "<p><strong>Actual Recipients in campaign_recipients table:</strong> $actualCount</p>";

if ($actualCount == 0) {
    echo "<p style='color: red; font-weight: bold;'>❌ NO RECIPIENTS FOUND!</p>";
    
    // Check if employees exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'employee'");
    $employeeCount = $stmt->fetch()['count'];
    echo "<p><strong>Employees in database:</strong> $employeeCount</p>";
    
    if ($employeeCount > 0) {
        echo "<p style='color: orange;'>⚠️ Employees exist but weren't added to campaign. Check PHP error logs for INSERT errors.</p>";
        echo "<p><strong>Error log location:</strong> " . ini_get('error_log') . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Recipients found in database</p>";
    
    // Show recipients
    $stmt = $pdo->prepare("SELECT * FROM campaign_recipients WHERE campaign_id = ?");
    $stmt->execute([$campaign['id']]);
    $recipients = $stmt->fetchAll();
    
    echo "<h3>Recipients:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Email</th><th>Status</th><th>Sent At</th></tr>";
    foreach ($recipients as $r) {
        $statusColor = $r['status'] === 'sent' ? 'green' : ($r['status'] === 'failed' ? 'red' : 'orange');
        echo "<tr>";
        echo "<td>{$r['id']}</td>";
        echo "<td>{$r['user_id']}</td>";
        echo "<td>{$r['email']}</td>";
        echo "<td style='color: $statusColor;'><strong>{$r['status']}</strong></td>";
        echo "<td>" . ($r['sent_at'] ?? 'Not sent') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check if emails were sent
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM campaign_recipients WHERE campaign_id = ? AND status = 'sent'");
$stmt->execute([$campaign['id']]);
$sentCount = $stmt->fetch()['count'];

echo "<p><strong>Emails Sent:</strong> $sentCount</p>";

if ($actualCount > 0 && $sentCount == 0 && $campaign['status'] !== 'draft') {
    echo "<p style='color: orange;'>⚠️ Recipients exist but no emails were sent. Check email sending logs.</p>";
}
?>

