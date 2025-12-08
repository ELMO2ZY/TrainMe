<?php
// Backend configuration (same as index.php)
$backend_config = [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'trainme_db',
        'username' => 'root',
        'password' => 'Eyadelmo2zy69'
    ]
];

function getDBConnection() {
    global $backend_config;
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $db = $backend_config['database'];
            $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, $db['username'], $db['password'], $options);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
    }
    return $pdo;
}

try {
    $pdo = getDBConnection();
    
    echo "<h2>Testing Campaigns Table Structure</h2>";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'campaigns'");
    $tableExists = $stmt->rowCount() > 0;
    echo "<p>Table 'campaigns' exists: " . ($tableExists ? "YES" : "NO") . "</p>";
    
    if ($tableExists) {
        // Get table structure
        echo "<h3>Table Structure:</h3>";
        $stmt = $pdo->query("DESCRIBE campaigns");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for 'name' column specifically
        $columnNames = array_column($columns, 'Field');
        echo "<h3>Column Check:</h3>";
        echo "<p>'name' column exists: " . (in_array('name', $columnNames) ? "YES" : "NO") . "</p>";
        echo "<p>'description' column exists: " . (in_array('description', $columnNames) ? "YES" : "NO") . "</p>";
        
        // Try a test query
        echo "<h3>Test Query:</h3>";
        try {
            $testStmt = $pdo->query("SELECT name FROM campaigns LIMIT 1");
            echo "<p style='color: green;'>✓ Query 'SELECT name FROM campaigns' works!</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Query failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Try with description
        try {
            $testStmt = $pdo->query("SELECT description FROM campaigns LIMIT 1");
            echo "<p style='color: green;'>✓ Query 'SELECT description FROM campaigns' works!</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Query failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

