<?php
/**
 * Test Recipient Insertion
 */

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

echo "<h2>Test Recipient Insertion</h2>";

// Get latest campaign
$stmt = $pdo->query("SELECT id, name FROM campaigns ORDER BY created_at DESC LIMIT 1");
$campaign = $stmt->fetch();

if (!$campaign) {
    echo "<p style='color: red;'>No campaigns found.</p>";
    exit;
}

echo "<h3>Testing with Campaign: {$campaign['name']} (ID: {$campaign['id']})</h3>";

// Get an employee
$stmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'employee' LIMIT 1");
$employee = $stmt->fetch();

if (!$employee) {
    echo "<p style='color: red;'>No employees found.</p>";
    exit;
}

echo "<h3>Testing with Employee: {$employee['name']} (ID: {$employee['id']}, Email: {$employee['email']})</h3>";

// Check table structure
echo "<h3>Table Structure:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE campaign_recipients");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Try to insert
if (isset($_GET['test_insert'])) {
    echo "<h3>Attempting Insert:</h3>";
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO campaign_recipients (campaign_id, user_id, email, name, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        
        $result = $stmt->execute([
            $campaign['id'],
            $employee['id'],
            $employee['email'],
            $employee['name']
        ]);
        
        if ($result) {
            echo "<p style='color: green; font-weight: bold;'>✓ Insert successful!</p>";
            echo "<p>Recipient ID: " . $pdo->lastInsertId() . "</p>";
            
            // Verify it was inserted
            $checkStmt = $pdo->prepare("SELECT * FROM campaign_recipients WHERE id = ?");
            $checkStmt->execute([$pdo->lastInsertId()]);
            $inserted = $checkStmt->fetch();
            
            if ($inserted) {
                echo "<p style='color: green;'>✓ Verified: Recipient found in database</p>";
                echo "<pre>";
                print_r($inserted);
                echo "</pre>";
            }
        } else {
            echo "<p style='color: red;'>✗ Insert failed (no exception but result is false)</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red; font-weight: bold;'>✗ INSERT FAILED!</p>";
        echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
        echo "<p><strong>Error Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>SQL State:</strong> " . $e->getCode() . "</p>";
        
        // Check if it's a foreign key constraint
        if ($e->getCode() == 23000 || strpos($e->getMessage(), 'foreign key') !== false) {
            echo "<p style='color: orange;'><strong>This looks like a foreign key constraint error.</strong></p>";
            echo "<p>Possible causes:</p>";
            echo "<ul>";
            echo "<li>Campaign ID {$campaign['id']} doesn't exist in campaigns table</li>";
            echo "<li>User ID {$employee['id']} doesn't exist in users table</li>";
            echo "<li>Data type mismatch between foreign key columns</li>";
            echo "</ul>";
        }
        
        // Check if it's a unique constraint
        if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'unique') !== false) {
            echo "<p style='color: orange;'><strong>This looks like a unique constraint error.</strong></p>";
            echo "<p>The recipient may already exist for this campaign.</p>";
        }
    }
} else {
    echo "<h3>Test Insert:</h3>";
    echo "<p><a href='?test_insert=1' style='display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>Test Insert Recipient</a></p>";
}

// Check existing recipients for this campaign
echo "<h3>Existing Recipients for Campaign {$campaign['id']}:</h3>";
$stmt = $pdo->prepare("SELECT * FROM campaign_recipients WHERE campaign_id = ?");
$stmt->execute([$campaign['id']]);
$existing = $stmt->fetchAll();

if (empty($existing)) {
    echo "<p style='color: orange;'>No recipients found for this campaign.</p>";
} else {
    echo "<p>Found " . count($existing) . " recipients:</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Campaign ID</th><th>User ID</th><th>Email</th><th>Name</th><th>Status</th></tr>";
    foreach ($existing as $rec) {
        echo "<tr>";
        echo "<td>{$rec['id']}</td>";
        echo "<td>{$rec['campaign_id']}</td>";
        echo "<td>{$rec['user_id']}</td>";
        echo "<td>{$rec['email']}</td>";
        echo "<td>{$rec['name']}</td>";
        echo "<td>{$rec['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>

