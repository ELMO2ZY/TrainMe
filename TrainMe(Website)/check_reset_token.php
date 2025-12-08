<?php
/**
 * Diagnostic script to check password reset token
 */

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("No token provided. Add ?token=YOUR_TOKEN to the URL");
}

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
            die("Database connection error: " . $e->getMessage());
        }
    }
    return $pdo;
}

$pdo = getDBConnection();

echo "<h2>Password Reset Token Diagnostic</h2>";
echo "<p><strong>Token:</strong> " . htmlspecialchars($token) . "</p>";
echo "<p><strong>Token Length:</strong> " . strlen($token) . " characters</p>";

// Check if table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'password_reset_tokens'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<p style='color: red;'><strong>❌ Table 'password_reset_tokens' does NOT exist!</strong></p>";
        echo "<p>Creating table now...</p>";
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_email (email),
            INDEX idx_user_id (user_id),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "<p style='color: green;'>✓ Table created successfully!</p>";
    } else {
        echo "<p style='color: green;'>✓ Table 'password_reset_tokens' exists</p>";
    }
    
    // Check for token
    $stmt = $pdo->prepare("SELECT * FROM password_reset_tokens WHERE token = ?");
    $stmt->execute([$token]);
    $tokenData = $stmt->fetch();
    
    if ($tokenData) {
        echo "<p style='color: green;'>✓ Token found in database</p>";
        echo "<pre>";
        print_r($tokenData);
        echo "</pre>";
        
        // Check expiration
        $expiresAt = strtotime($tokenData['expires_at']);
        $now = time();
        $isExpired = $expiresAt < $now;
        
        echo "<p><strong>Expires At:</strong> " . $tokenData['expires_at'] . "</p>";
        echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
        echo "<p><strong>Is Expired:</strong> " . ($isExpired ? '<span style="color: red;">YES</span>' : '<span style="color: green;">NO</span>') . "</p>";
        echo "<p><strong>Is Used:</strong> " . ($tokenData['used'] == 1 ? '<span style="color: red;">YES</span>' : '<span style="color: green;">NO</span>') . "</p>";
        
        // Check user
        $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $stmt->execute([$tokenData['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p style='color: green;'>✓ User found</p>";
            echo "<pre>";
            print_r($user);
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>❌ User not found for user_id: " . $tokenData['user_id'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Token NOT found in database</p>";
        
        // Show all tokens (for debugging)
        $stmt = $pdo->query("SELECT token, email, expires_at, used, created_at FROM password_reset_tokens ORDER BY created_at DESC LIMIT 10");
        $allTokens = $stmt->fetchAll();
        
        if (count($allTokens) > 0) {
            echo "<p><strong>Recent tokens in database:</strong></p>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Token (first 20 chars)</th><th>Email</th><th>Expires</th><th>Used</th><th>Created</th></tr>";
            foreach ($allTokens as $t) {
                echo "<tr>";
                echo "<td>" . substr($t['token'], 0, 20) . "...</td>";
                echo "<td>" . htmlspecialchars($t['email']) . "</td>";
                echo "<td>" . $t['expires_at'] . "</td>";
                echo "<td>" . ($t['used'] ? 'YES' : 'NO') . "</td>";
                echo "<td>" . $t['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No tokens found in database. The table is empty.</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}

