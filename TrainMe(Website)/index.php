<?php
// TrainMe Backend - Eyad's Backend Logic
// Start output buffering immediately to catch any output
ob_start();

// Suppress any warnings/notices that might break JSON responses
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// Include email helper (suppress errors to prevent breaking JSON)
try {
    require_once __DIR__ . '/email_helper.php';
} catch (Throwable $e) {
    error_log("Email helper include error: " . $e->getMessage());
}

// Backend configuration
$backend_config = [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'trainme_db',
        'username' => 'root',
        'password' => 'Eyadelmo2zy69'
    ],
    'email' => [
        'sendgrid_api_key' => 'your_sendgrid_key_here',
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => 'your_email@gmail.com',
        'smtp_password' => 'your_app_password'
    ],
    'tracking' => [
        'base_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
    ]
];

// Database connection function
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
            // Return null on error - will be handled by calling code
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
    }
    
    return $pdo;
}

// Function to load training progress from database into session
function loadTrainingProgressFromDB($userId) {
    $pdo = getDBConnection();
    if ($pdo === null) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT module_key, score, completed_at 
            FROM training_progress 
            WHERE user_id = ? 
            ORDER BY completed_at DESC
        ");
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll();
        
        $progress = [];
        foreach ($results as $row) {
            $progress[$row['module_key']] = [
                'score' => (int)$row['score'],
                'completed_at' => $row['completed_at']
            ];
        }
        
        // Store in session
        $_SESSION['training_progress'] = $progress;
        
        return $progress;
    } catch (PDOException $e) {
        error_log("Error loading training progress: " . $e->getMessage());
        return [];
    }
}

// API Endpoints for Frontend (Amr)
if (isset($_GET['api'])) {
    // Clean any output that may have been generated (but keep the buffer)
    if (ob_get_level() > 0) {
        ob_clean();
    }
    
    header('Content-Type: application/json');
    
    switch($_GET['api']) {
        case 'auth':
            handleAuthAPI();
            break;
        case 'campaigns':
            handleCampaignsAPI();
            break;
        case 'tracking':
            handleTrackingAPI();
            break;
        case 'email':
            handleEmailAPI();
            break;
        case 'users':
            handleUsersAPI();
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'API endpoint not found']);
    }
    
    // Flush and send the response, then exit
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    exit;
}

// Authentication API
function handleAuthAPI() {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch($action) {
        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                http_response_code(400);
                echo json_encode(['error' => 'Email and password are required']);
                return;
            }
            
            $pdo = getDBConnection();
            if ($pdo === null) {
                // Fallback to hardcoded credentials if DB connection fails
                if ($email === 'admin@trainme.com' && $password === 'admin123') {
                    $_SESSION['user_id'] = 1;
                    $_SESSION['user_role'] = 'admin';
                    $_SESSION['user_name'] = 'Admin User';
                    echo json_encode(['success' => true, 'role' => 'admin']);
                } elseif ($email === 'employee@trainme.com' && $password === 'emp123') {
                    $_SESSION['user_id'] = 2;
                    $_SESSION['user_role'] = 'employee';
                    $_SESSION['user_name'] = 'Employee User';
                    echo json_encode(['success' => true, 'role' => 'employee']);
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Invalid credentials']);
                }
                return;
            }
            
            try {
                // Get the selected role from the form
                $selectedRole = $_POST['role'] ?? '';
                
                if (empty($selectedRole)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Please select a role (Employee or Admin)']);
                    return;
                }
                
                // Check both email AND role
                $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? AND role = ?");
                $stmt->execute([$email, $selectedRole]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['name'];
                    // Store last login time for this session
                    $_SESSION['last_login'] = date('Y-m-d H:i:s');
                    
                    // Load training progress from database into session
                    loadTrainingProgressFromDB($user['id']);
                    
                    echo json_encode([
                        'success' => true, 
                        'role' => $user['role'],
                        'name' => $user['name']
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Invalid credentials or role mismatch. Please check your email, password, and selected role.']);
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Database error. Please try again.']);
            }
            break;
            
        case 'register':
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'employee';
            
            // Validation
            if (empty($name) || empty($email) || empty($password)) {
                http_response_code(400);
                echo json_encode(['error' => 'Name, email, and password are required']);
                return;
            }
            
            if (strlen($name) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Name must be at least 2 characters long']);
                return;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid email address']);
                return;
            }
            
            if (strlen($password) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'Password must be at least 6 characters long']);
                return;
            }
            
            if (!in_array($role, ['employee', 'admin'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid role selected']);
                return;
            }
            
            $pdo = getDBConnection();
            if ($pdo === null) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed. Please try again later.']);
                return;
            }
            
            try {
                // Check if email+role combination already exists (allow same email with different roles)
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND role = ?");
                $stmt->execute([$email, $role]);
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['error' => 'This email is already registered as ' . $role . '. Please use a different email or sign in.']);
                    return;
                }
                
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Check if password_hash column exists, then insert accordingly
                try {
                    // Try with password_hash column first
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, password_hash, role) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $hashedPassword, $hashedPassword, $role]);
                } catch (PDOException $e) {
                    // If password_hash column doesn't exist, try without it
                    if (strpos($e->getMessage(), 'password_hash') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
                        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$name, $email, $hashedPassword, $role]);
                    } else {
                        // Re-throw if it's a different error
                        throw $e;
                    }
                }
                
                // Get the new user ID
                $userId = $pdo->lastInsertId();
                
                // Check if this registration is from an admin (admin creating user)
                $isAdminCreation = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
                
                // Only auto-login if it's a self-registration (not admin creating user)
                if (!$isAdminCreation) {
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_role'] = $role;
                    $_SESSION['user_name'] = $name;
                    // Initialize empty training progress for new user
                    $_SESSION['training_progress'] = [];
                }
                
                // Send welcome email with temporary password (don't let email errors break registration)
                $emailSent = false;
                $emailError = null;
                try {
                    if (class_exists('TrainMeEmail')) {
                        $emailHelper = new TrainMeEmail();
                        // Pass the temporary password to include in email
                        $result = $emailHelper->sendWelcomeEmail($email, $name, $role, $password);
                        $emailSent = is_array($result) && ($result['success'] ?? false);
                        if (!$emailSent) {
                            $emailError = $result['message'] ?? 'Unknown error';
                            error_log("Welcome email failed for $email: " . $emailError);
                        } else {
                            error_log("Welcome email sent successfully to: $email");
                        }
                    } else {
                        error_log("TrainMeEmail class not found - email helper may not be loaded correctly");
                    }
                } catch (Exception $e) {
                    // Log error but don't fail registration
                    $emailError = $e->getMessage();
                    error_log("Welcome email error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
                } catch (Throwable $e) {
                    // Catch any fatal errors too
                    $emailError = $e->getMessage();
                    error_log("Welcome email fatal error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
                }
                
                // Always return success JSON response
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'admin_created' => $isAdminCreation,
                    'email_sent' => $emailSent,
                    'message' => 'Account created successfully',
                    'role' => $role,
                    'name' => $name
                ]);
                return;
            } catch (PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                http_response_code(500);
                // Return more detailed error for debugging (remove in production)
                echo json_encode([
                    'error' => 'Registration failed: ' . $e->getMessage(),
                    'details' => 'Check server logs for more information'
                ]);
                return;
            } catch (Throwable $e) {
                // Catch any other unexpected errors
                error_log("Registration fatal error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'error' => 'Registration failed. Please try again.',
                    'details' => 'An unexpected error occurred'
                ]);
                return;
            }
            break;
            
        case 'forgot_password':
            $email = $_POST['email'] ?? '';
            $role = $_POST['role'] ?? '';
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Valid email address is required']);
                return;
            }
            
            if (empty($role) || !in_array($role, ['employee', 'admin'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Please select a role (Employee or Admin)']);
                return;
            }
            
            $pdo = getDBConnection();
            if ($pdo === null) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                return;
            }
            
            try {
                // Check if user exists with this email and role
                $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ? AND role = ?");
                $stmt->execute([$email, $role]);
                $user = $stmt->fetch();
                
                // Always return success message (security: don't reveal if email exists)
                if ($user) {
                    // Generate secure random token
                    $token = bin2hex(random_bytes(32));
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Check if password_reset_tokens table exists, create if not
                    try {
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
                    } catch (PDOException $e) {
                        // Table might already exist, continue
                        error_log("Password reset table check: " . $e->getMessage());
                    }
                    
                    // Invalidate any existing tokens for this user
                    $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE user_id = ? AND used = 0");
                    $stmt->execute([$user['id']]);
                    
                    // Insert new token
                    $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, email, token, expires_at) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$user['id'], $email, $token, $expiresAt]);
                    
                    // Send password reset email
                    if (class_exists('TrainMeEmail')) {
                        $emailHelper = new TrainMeEmail();
                        $result = $emailHelper->sendPasswordResetEmail($email, $user['name'], $token);
                        if (is_array($result) && ($result['success'] ?? false)) {
                            error_log("Password reset email sent to: $email");
                        } else {
                            error_log("Password reset email failed for: $email - " . ($result['message'] ?? 'Unknown error'));
                        }
                    }
                }
                
                // Always return success (security: don't reveal if email exists)
                echo json_encode([
                    'success' => true,
                    'message' => 'If an account with that email exists, a password reset link has been sent.'
                ]);
            } catch (PDOException $e) {
                error_log("Password reset request error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to process password reset request']);
            }
            break;
            
        case 'reset_password':
            $token = $_POST['token'] ?? '';
            $newPassword = $_POST['password'] ?? '';
            
            if (empty($token) || empty($newPassword)) {
                http_response_code(400);
                echo json_encode(['error' => 'Token and new password are required']);
                return;
            }
            
            if (strlen($newPassword) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'Password must be at least 6 characters long']);
                return;
            }
            
            $pdo = getDBConnection();
            if ($pdo === null) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                return;
            }
            
            try {
                // Ensure password_reset_tokens table exists
                try {
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
                } catch (PDOException $e) {
                    // Table might already exist, continue
                    error_log("Password reset table check: " . $e->getMessage());
                }
                
                // First check if token exists at all
                $stmt = $pdo->prepare("SELECT user_id, email, expires_at, used FROM password_reset_tokens WHERE token = ?");
                $stmt->execute([$token]);
                $tokenRow = $stmt->fetch();
                
                if (!$tokenRow) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid reset token. Please request a new password reset link.']);
                    return;
                }
                
                if ($tokenRow['used'] == 1) {
                    http_response_code(400);
                    echo json_encode(['error' => 'This reset link has already been used. Please request a new password reset.']);
                    return;
                }
                
                // Check expiration
                $expiresAt = strtotime($tokenRow['expires_at']);
                $now = time();
                if ($expiresAt < $now) {
                    http_response_code(400);
                    echo json_encode(['error' => 'This reset link has expired. Please request a new password reset.']);
                    return;
                }
                
                // Get user info
                $stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
                $stmt->execute([$tokenRow['user_id']]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    http_response_code(400);
                    echo json_encode(['error' => 'User account not found. Please request a new password reset.']);
                    return;
                }
                
                $tokenData = [
                    'user_id' => $tokenRow['user_id'],
                    'email' => $tokenRow['email'],
                    'role' => $user['role']
                ];
                
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $tokenData['user_id']]);
                
                // Mark token as used
                $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
                $stmt->execute([$token]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Password has been reset successfully. You can now login with your new password.'
                ]);
            } catch (PDOException $e) {
                error_log("Password reset error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to reset password']);
            }
            break;
            
        case 'logout':
            session_destroy();
            echo json_encode(['success' => true]);
            break;
            
        case 'check':
            if (isset($_SESSION['user_id'])) {
                echo json_encode([
                    'authenticated' => true,
                    'user_id' => $_SESSION['user_id'],
                    'role' => $_SESSION['user_role'],
                    'name' => $_SESSION['user_name']
                ]);
            } else {
                echo json_encode(['authenticated' => false]);
            }
            break;
    }
}

// Function to send campaign emails
function sendCampaignEmails($campaignId, $pdo) {
    try {
        // Get campaign details
        $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ?");
        $stmt->execute([$campaignId]);
        $campaign = $stmt->fetch();
        
        if (!$campaign) {
            return 0;
        }
        
        // Get pending recipients
        $stmt = $pdo->prepare("
            SELECT cr.*, u.name, u.email 
            FROM campaign_recipients cr
            JOIN users u ON cr.user_id = u.id
            WHERE cr.campaign_id = ? AND cr.status = 'pending'
        ");
        $stmt->execute([$campaignId]);
        $recipients = $stmt->fetchAll();
        
        error_log("sendCampaignEmails: Found " . count($recipients) . " pending recipients for campaign $campaignId");
        
        if (empty($recipients)) {
            error_log("sendCampaignEmails: No pending recipients found. Checking all recipients for campaign $campaignId");
            // Check if there are any recipients at all
            $checkStmt = $pdo->prepare("SELECT COUNT(*) as total FROM campaign_recipients WHERE campaign_id = ?");
            $checkStmt->execute([$campaignId]);
            $totalRecipients = $checkStmt->fetch()['total'];
            error_log("sendCampaignEmails: Total recipients in campaign_recipients table: $totalRecipients");
            
            // Check status breakdown
            $statusStmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM campaign_recipients WHERE campaign_id = ? GROUP BY status");
            $statusStmt->execute([$campaignId]);
            $statusBreakdown = $statusStmt->fetchAll();
            error_log("sendCampaignEmails: Status breakdown: " . json_encode($statusBreakdown));
        }
        
        $sentCount = 0;
        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        
        // Include email helper
        if (class_exists('TrainMeEmail')) {
            $emailHelper = new TrainMeEmail();
        } else {
            error_log("TrainMeEmail class not found - cannot send campaign emails");
            return 0;
        }
        
        // Disable SMTP debug for faster sending (only log errors)
        error_log("Starting to send " . count($recipients) . " campaign emails...");
        
        foreach ($recipients as $index => $recipient) {
            try {
                error_log("Processing recipient: {$recipient['email']} (ID: {$recipient['id']})");
                
                // Get the token from the recipient record (it should already be stored)
                $tokenStmt = $pdo->prepare("SELECT token FROM campaign_recipients WHERE id = ?");
                $tokenStmt->execute([$recipient['id']]);
                $tokenData = $tokenStmt->fetch();
                $trackingToken = $tokenData['token'] ?? bin2hex(random_bytes(16));
                
                // Generate tracking link with proper URL encoding
                $trackingUrl = $baseUrl . '/campaign_track.php?token=' . urlencode($trackingToken) . '&campaign=' . $campaignId . '&recipient=' . $recipient['id'];
                
                // Replace placeholders in email content
                $emailContent = $campaign['email_content'];
                $emailContent = str_replace('{name}', $recipient['name'] ?? 'User', $emailContent);
                $emailContent = str_replace('{link}', $trackingUrl, $emailContent);
                
                error_log("Sending email to: {$recipient['email']} with subject: {$campaign['subject']}");
                
                // Send email
                $result = $emailHelper->sendCampaignEmail(
                    $recipient['email'],
                    $campaign['subject'],
                    $emailContent,
                    $campaign['sender_name'] ?? 'Security Team',
                    $campaign['sender_email'] ?? 'noreply@trainme.com'
                );
                
                error_log("Email send result for {$recipient['email']}: " . json_encode($result));
                
                if (is_array($result) && ($result['success'] ?? false)) {
                    // Update recipient status
                    $stmt = $pdo->prepare("
                        UPDATE campaign_recipients 
                        SET status = 'sent', sent_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$recipient['id']]);
                    
                    $sentCount++;
                    // Log progress every 5 emails to reduce log spam
                    if ($sentCount % 5 == 0 || $sentCount == count($recipients)) {
                        error_log("Email progress: $sentCount/" . count($recipients) . " sent");
                    }
                } else {
                    // Mark as failed
                    $stmt = $pdo->prepare("
                        UPDATE campaign_recipients 
                        SET status = 'failed' 
                        WHERE id = ?
                    ");
                    $stmt->execute([$recipient['id']]);
                    error_log("Failed to send to {$recipient['email']}: " . ($result['message'] ?? 'Unknown error'));
                }
            } catch (Exception $e) {
                error_log("Exception sending to {$recipient['email']}: " . $e->getMessage());
                
                // Mark as failed
                try {
                    $stmt = $pdo->prepare("
                        UPDATE campaign_recipients 
                        SET status = 'failed' 
                        WHERE id = ?
                    ");
                    $stmt->execute([$recipient['id']]);
                } catch (PDOException $updateError) {
                    error_log("Failed to update recipient status to 'failed': " . $updateError->getMessage());
                }
            }
        }
        
        // Update campaign sent count
        $stmt = $pdo->prepare("UPDATE campaigns SET total_sent = ? WHERE id = ?");
        $stmt->execute([$sentCount, $campaignId]);
        
        return $sentCount;
    } catch (PDOException $e) {
        error_log("Error in sendCampaignEmails: " . $e->getMessage());
        return 0;
    }
}

// Campaigns API
function handleCampaignsAPI() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        return;
    }
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch($action) {
        case 'list':
            // Load campaigns from database
            $pdo = getDBConnection();
            $campaigns = [];

            if ($pdo !== null) {
                try {
                    $stmt = $pdo->query("
                        SELECT 
                            c.id, 
                            c.name, 
                            c.status, 
                            c.total_recipients as recipients,
                            c.total_clicks as clicks,
                            c.created_at,
                            c.template
                        FROM campaigns c
                        ORDER BY c.created_at DESC
                    ");
                    $campaigns = $stmt->fetchAll();
                    
                    // Sync click counts with actual tracking records
                    foreach ($campaigns as &$campaign) {
                        $campaignId = (int)$campaign['id'];
                        $totalClicksFromDB = (int)($campaign['clicks'] ?? 0);
                        
                        // Get actual click count from campaign_tracking
                        try {
                            $clickCountStmt = $pdo->prepare("
                                SELECT COUNT(*) as actual_clicks 
                                FROM campaign_tracking 
                                WHERE campaign_id = ? AND action_type = 'link_clicked'
                            ");
                            $clickCountStmt->execute([$campaignId]);
                            $clickCountData = $clickCountStmt->fetch();
                            $actualClickCount = (int)($clickCountData['actual_clicks'] ?? 0);
                            
                            // If there's a mismatch, update the campaigns table
                            if ($totalClicksFromDB != $actualClickCount) {
                                error_log("Syncing click count for campaign $campaignId: DB=$totalClicksFromDB, Actual=$actualClickCount");
                                $syncStmt = $pdo->prepare("UPDATE campaigns SET total_clicks = ? WHERE id = ?");
                                $syncStmt->execute([$actualClickCount, $campaignId]);
                                $totalClicksFromDB = $actualClickCount;
                            }
                        } catch (PDOException $e) {
                            // If campaign_tracking table doesn't exist, just use the DB value
                            error_log("Error syncing click count for campaign $campaignId: " . $e->getMessage());
                        }
                        
                        // Format campaigns for frontend
                        $campaign['recipients'] = (int)($campaign['recipients'] ?? 0);
                        $campaign['clicks'] = $totalClicksFromDB;
                    }
                } catch (PDOException $e) {
                    // If campaigns table doesn't exist yet, fall back to empty list
                    error_log("Error fetching campaigns: " . $e->getMessage());
                    $campaigns = [];
                }
            }

            echo json_encode(['success' => true, 'campaigns' => $campaigns]);
            break;
            
        case 'create':
            if ($_SESSION['user_role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                return;
            }
            
            $pdo = getDBConnection();
            if ($pdo === null) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                return;
            }
            
            try {
                $name = trim($_POST['name'] ?? '');
                $template = $_POST['template'] ?? '';
                $subject = trim($_POST['subject'] ?? '');
                $emailContent = $_POST['email_content'] ?? '';
                $senderName = trim($_POST['sender_name'] ?? 'Security Team');
                $senderEmail = trim($_POST['sender_email'] ?? 'noreply@trainme.com');
                $isDraft = ($_POST['is_draft'] ?? '0') === '1';
                $recipientType = $_POST['recipient_type'] ?? 'all';
                
                // Validate required fields
                if (empty($name) || empty($template) || empty($subject) || empty($emailContent)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing required fields']);
                    return;
                }
                
                // Create campaign
                $status = $isDraft ? 'draft' : 'active';
                
                // Check actual table structure and use correct column
                try {
                    $checkStmt = $pdo->query("DESCRIBE campaigns");
                    $allColumns = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
                    $columnNames = array_column($allColumns, 'Field');
                    
                    error_log("Available columns in campaigns table: " . implode(', ', $columnNames));
                    error_log("Current database: " . $pdo->query("SELECT DATABASE()")->fetchColumn());
                    
                    // Determine which name column to use
                    $nameColumn = null;
                    if (in_array('name', $columnNames)) {
                        $nameColumn = 'name';
                    } elseif (in_array('description', $columnNames)) {
                        $nameColumn = 'description';
                    } else {
                        throw new Exception("Neither 'name' nor 'description' column found in campaigns table. Available columns: " . implode(', ', $columnNames));
                    }
                    
                    error_log("Using column: " . $nameColumn);
                    
                    // Build INSERT statement with available columns
                    $insertColumns = [];
                    $insertValues = [];
                    $placeholders = [];
                    
                    // Add name/description
                    $insertColumns[] = $nameColumn;
                    $insertValues[] = $name;
                    $placeholders[] = '?';
                    
                    // Add other columns if they exist
                    $requiredColumns = [
                        'template' => $template,
                        'subject' => $subject,
                        'email_content' => $emailContent,
                        'sender_name' => $senderName,
                        'sender_email' => $senderEmail,
                        'status' => $status,
                        'created_by' => $_SESSION['user_id']
                    ];
                    
                    foreach ($requiredColumns as $colName => $colValue) {
                        if (in_array($colName, $columnNames)) {
                            $insertColumns[] = $colName;
                            $insertValues[] = $colValue;
                            $placeholders[] = '?';
                        } else {
                            error_log("Warning: Column '$colName' not found, skipping");
                        }
                    }
                    
                    // Execute INSERT
                    $sql = "INSERT INTO campaigns (" . implode(', ', $insertColumns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    error_log("Executing SQL: " . $sql);
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($insertValues);
                } catch (PDOException $e) {
                    error_log("Campaign creation PDO error: " . $e->getMessage());
                    throw $e;
                }
                
                $campaignId = $pdo->lastInsertId();
                
                // Get recipients
                $recipients = [];
                try {
                    if ($recipientType === 'all') {
                        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE role = 'employee'");
                        $stmt->execute();
                        $recipients = $stmt->fetchAll();
                        error_log("=== Fetched " . count($recipients) . " employees for campaign ===");
                        if (count($recipients) > 0) {
                            error_log("Employee list: " . implode(', ', array_map(function($r) { return $r['name'] . " ({$r['email']})"; }, $recipients)));
                        }
                    } else {
                        $recipientIds = json_decode($_POST['recipients'] ?? '[]', true);
                        if (!empty($recipientIds)) {
                            $placeholders = implode(',', array_fill(0, count($recipientIds), '?'));
                            $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id IN ($placeholders) AND role = 'employee'");
                            $stmt->execute($recipientIds);
                            $recipients = $stmt->fetchAll();
                            error_log("=== Fetched " . count($recipients) . " selected recipients for campaign ===");
                        } else {
                            error_log("⚠️ No recipient IDs provided for 'selected' recipient type");
                        }
                    }
                } catch (PDOException $e) {
                    error_log("✗ ERROR fetching recipients: " . $e->getMessage());
                    error_log("   SQL Error Code: " . $e->getCode());
                    // Continue with empty recipients list
                    $recipients = [];
                }
                
                // Add recipients to campaign_recipients table
                $totalRecipients = 0;
                if (!empty($recipients)) {
                    error_log("=== START: Adding " . count($recipients) . " recipients to campaign_recipients (Campaign ID: $campaignId) ===");
                    
                    // Use INSERT IGNORE to handle duplicate entries gracefully
                    foreach ($recipients as $recipient) {
                        try {
                            // Check if recipient already exists (unique constraint on campaign_id + user_id)
                            $checkStmt = $pdo->prepare("SELECT id FROM campaign_recipients WHERE campaign_id = ? AND user_id = ?");
                            $checkStmt->execute([$campaignId, $recipient['id']]);
                            if ($checkStmt->fetch()) {
                                error_log("⚠️ Recipient {$recipient['id']} already exists for this campaign, skipping");
                                $totalRecipients++; // Count it as added
                                continue;
                            }
                            
                            // Insert recipient - table structure: campaign_id, user_id, email, status, token (token is NOT NULL)
                            $insertCampaignId = (int)$campaignId;
                            $insertUserId = (int)$recipient['id'];
                            $insertEmail = trim($recipient['email']);
                            $insertToken = bin2hex(random_bytes(32)); // Generate unique token for tracking
                            
                            error_log("   Inserting: campaign_id=$insertCampaignId, user_id=$insertUserId, email=$insertEmail, token=$insertToken");
                            
                            // Insert with all required columns (token is NOT NULL)
                            // Use explicit error mode to catch any issues
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            
                            $stmt = $pdo->prepare("
                                INSERT INTO campaign_recipients (campaign_id, user_id, email, status, token)
                                VALUES (?, ?, ?, 'pending', ?)
                            ");
                            
                            // Execute and verify
                            $result = $stmt->execute([
                                $insertCampaignId, 
                                $insertUserId, 
                                $insertEmail,
                                $insertToken
                            ]);
                            
                            // Verify insertion
                            $insertedId = $pdo->lastInsertId();
                            if ($insertedId > 0) {
                                $totalRecipients++;
                                error_log("✓ Successfully added recipient: {$recipient['name']} ({$insertEmail}) - Recipient ID: $insertedId");
                                
                                // Double-check it was actually inserted
                                $verifyStmt = $pdo->prepare("SELECT id FROM campaign_recipients WHERE id = ?");
                                $verifyStmt->execute([$insertedId]);
                                if (!$verifyStmt->fetch()) {
                                    error_log("⚠️ WARNING: Insert reported success but recipient not found in database!");
                                }
                            } else {
                                $errorInfo = $stmt->errorInfo();
                                error_log("✗ INSERT returned false for recipient {$insertUserId}");
                                error_log("   PDO Error Info: " . print_r($errorInfo, true));
                                throw new Exception("Failed to insert recipient - lastInsertId is 0");
                            }
                        } catch (PDOException $e) {
                            $errorCode = $e->getCode();
                            $errorMsg = $e->getMessage();
                            
                            error_log("✗ ERROR adding recipient {$recipient['id']} ({$recipient['email']}):");
                            error_log("   Error Code: $errorCode");
                            error_log("   Error Message: $errorMsg");
                            
                            // If it's a duplicate entry, count it as success
                            if ($errorCode == 23000 || strpos($errorMsg, 'Duplicate') !== false || strpos($errorMsg, 'unique') !== false) {
                                error_log("   (Duplicate entry - recipient already exists, counting as added)");
                                $totalRecipients++;
                            } else {
                                // Log full error for debugging
                                error_log("   Full error details: " . print_r($e->errorInfo ?? [], true));
                            }
                        }
                    }
                    error_log("=== END: Added $totalRecipients out of " . count($recipients) . " recipients ===");
                } else {
                    error_log("⚠️ WARNING: No recipients to add - recipients array is empty!");
                    error_log("   This means no employees were found or recipient selection failed.");
                }
                
                // Update campaign with recipient count (only if column exists)
                try {
                    $checkCols = $pdo->query("DESCRIBE campaigns");
                    $campaignColumns = array_column($checkCols->fetchAll(PDO::FETCH_ASSOC), 'Field');
                    
                    if (in_array('total_recipients', $campaignColumns)) {
                        $stmt = $pdo->prepare("UPDATE campaigns SET total_recipients = ? WHERE id = ?");
                        $stmt->execute([$totalRecipients, $campaignId]);
                    }
                } catch (PDOException $e) {
                    error_log("Error updating total_recipients: " . $e->getMessage());
                    // Continue - this is not critical
                }
                
                // If not draft, send emails
                $sentCount = 0;
                if (!$isDraft) {
                    if ($totalRecipients > 0) {
                        try {
                            error_log("Attempting to send campaign emails to $totalRecipients recipients");
                            $sentCount = sendCampaignEmails($campaignId, $pdo);
                            error_log("Campaign emails sent: $sentCount out of $totalRecipients");
                        } catch (Exception $e) {
                            error_log("Error sending campaign emails: " . $e->getMessage());
                            error_log("Stack trace: " . $e->getTraceAsString());
                            // Continue - campaign is created, emails can be sent later
                        }
                    } else {
                        error_log("No recipients to send campaign emails to (totalRecipients: $totalRecipients)");
                    }
                } else {
                    error_log("Campaign is a draft, skipping email sending");
                }
                
                // Return response with detailed info
                $response = [
                    'success' => true,
                    'campaign_id' => $campaignId,
                    'message' => $isDraft ? 'Campaign saved as draft' : "Campaign created and sent to $sentCount recipients",
                    'recipients' => $totalRecipients,
                    'sent' => $sentCount
                ];
                
                // Add warning if no recipients were added
                if ($totalRecipients == 0 && !empty($recipients)) {
                    $response['warning'] = 'Campaign created but no recipients were added. Check error logs for details.';
                    error_log("⚠️ CRITICAL: Campaign created but 0 recipients added despite " . count($recipients) . " recipients found!");
                } elseif ($totalRecipients == 0 && empty($recipients)) {
                    $response['warning'] = 'No employees found to send campaign to.';
                    error_log("⚠️ WARNING: No employees found in database for campaign");
                }
                
                echo json_encode($response);
            } catch (PDOException $e) {
                error_log("Campaign creation error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create campaign: ' . $e->getMessage()]);
            }
            break;
            
        case 'send':
            if ($_SESSION['user_role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                return;
            }
            
            $pdo = getDBConnection();
            if ($pdo === null) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                return;
            }
            
            $campaignId = (int)($_POST['campaign_id'] ?? 0);
            
            if ($campaignId <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid campaign ID']);
                return;
            }
            
            try {
                // Get campaign
                $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ?");
                $stmt->execute([$campaignId]);
                $campaign = $stmt->fetch();
                
                if (!$campaign) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Campaign not found']);
                    return;
                }
                
                // Send emails
                $sentCount = sendCampaignEmails($campaignId, $pdo);
                
                // Update campaign status
                $stmt = $pdo->prepare("UPDATE campaigns SET status = 'active', sent_at = NOW() WHERE id = ?");
                $stmt->execute([$campaignId]);
                
                echo json_encode([
                    'success' => true,
                    'message' => "Campaign sent to $sentCount recipients",
                    'sent_count' => $sentCount
                ]);
            } catch (PDOException $e) {
                error_log("Campaign send error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to send campaign: ' . $e->getMessage()]);
            }
            break;
            
        case 'delete':
            if ($_SESSION['user_role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                return;
            }
            
            $pdo = getDBConnection();
            if ($pdo === null) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                return;
            }
            
            $campaignId = (int)($_POST['campaign_id'] ?? 0);
            
            if ($campaignId <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid campaign ID']);
                return;
            }
            
            try {
                // Check if campaign exists
                $stmt = $pdo->prepare("SELECT id, name FROM campaigns WHERE id = ?");
                $stmt->execute([$campaignId]);
                $campaign = $stmt->fetch();
                
                if (!$campaign) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Campaign not found']);
                    return;
                }
                
                // Delete related records first (foreign key constraints)
                $pdo->beginTransaction();
                
                // Delete campaign tracking
                $stmt = $pdo->prepare("DELETE FROM campaign_tracking WHERE campaign_id = ?");
                $stmt->execute([$campaignId]);
                
                // Delete campaign recipients
                $stmt = $pdo->prepare("DELETE FROM campaign_recipients WHERE campaign_id = ?");
                $stmt->execute([$campaignId]);
                
                // Delete campaign
                $stmt = $pdo->prepare("DELETE FROM campaigns WHERE id = ?");
                $stmt->execute([$campaignId]);
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Campaign deleted successfully'
                ]);
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Campaign delete error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete campaign: ' . $e->getMessage()]);
            }
            break;
            
        case 'details':
            if ($_SESSION['user_role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                return;
            }
            
            $pdo = getDBConnection();
            if ($pdo === null) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                return;
            }
            
            $campaignId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
            
            if ($campaignId <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid campaign ID']);
                return;
            }
            
            try {
                // Get campaign details
                $stmt = $pdo->prepare("
                    SELECT c.*, u.name as created_by_name
                    FROM campaigns c
                    LEFT JOIN users u ON c.created_by = u.id
                    WHERE c.id = ?
                ");
                $stmt->execute([$campaignId]);
                $campaign = $stmt->fetch();
                
                if (!$campaign) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Campaign not found']);
                    return;
                }
                
                // Get recipients with their details
                $stmt = $pdo->prepare("
                    SELECT 
                        cr.id,
                        cr.email,
                        cr.status,
                        cr.sent_at,
                        cr.name as recipient_name,
                        u.name as user_name,
                        u.id as user_id
                    FROM campaign_recipients cr
                    LEFT JOIN users u ON cr.user_id = u.id
                    WHERE cr.campaign_id = ?
                    ORDER BY cr.email ASC
                ");
                $stmt->execute([$campaignId]);
                $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get click tracking data - who clicked
                $stmt = $pdo->prepare("
                    SELECT 
                        ct.id,
                        ct.recipient_id,
                        ct.user_id,
                        ct.ip_address,
                        ct.user_agent,
                        ct.created_at as clicked_at,
                        cr.email,
                        cr.name as recipient_name,
                        u.name as user_name,
                        u.email as user_email
                    FROM campaign_tracking ct
                    INNER JOIN campaign_recipients cr ON ct.recipient_id = cr.id
                    LEFT JOIN users u ON ct.user_id = u.id
                    WHERE ct.campaign_id = ? AND ct.action_type = 'link_clicked'
                    ORDER BY ct.created_at DESC
                ");
                $stmt->execute([$campaignId]);
                $clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Ensure all required fields exist and log for debugging
                error_log("=== CLICKS QUERY RESULT ===");
                error_log("Found " . count($clicks) . " clicks for campaign $campaignId");
                
                foreach ($clicks as &$click) {
                    $click['name'] = $click['user_name'] ?? $click['recipient_name'] ?? 'Unknown';
                    $click['email'] = $click['email'] ?? $click['user_email'] ?? 'N/A';
                    $click['ip_address'] = $click['ip_address'] ?? 'N/A';
                    
                    // Log first click for debugging
                    if ($click === reset($clicks)) {
                        error_log("First click data: " . json_encode($click));
                    }
                }
                unset($click);
                
                // Count recipients by status
                $statusCounts = [
                    'pending' => 0,
                    'sent' => 0,
                    'clicked' => 0,
                    'reported' => 0,
                    'failed' => 0
                ];
                
                foreach ($recipients as $recipient) {
                    $status = $recipient['status'] ?? 'pending';
                    if (isset($statusCounts[$status])) {
                        $statusCounts[$status]++;
                    }
                }
                
                // Get actual click count from campaigns table (to match campaign list)
                // Also sync it with actual tracking records if there's a mismatch
                $clickCountStmt = $pdo->prepare("SELECT total_clicks FROM campaigns WHERE id = ?");
                $clickCountStmt->execute([$campaignId]);
                $clickCountData = $clickCountStmt->fetch();
                $totalClicksFromDB = (int)($clickCountData['total_clicks'] ?? 0);
                $actualClickCount = count($clicks);
                
                // If there's a mismatch, update the campaigns table to match actual clicks
                if ($totalClicksFromDB != $actualClickCount && $actualClickCount > 0) {
                    error_log("Syncing click count: campaigns.total_clicks=$totalClicksFromDB, actual clicks=$actualClickCount");
                    $syncStmt = $pdo->prepare("UPDATE campaigns SET total_clicks = ? WHERE id = ?");
                    $syncStmt->execute([$actualClickCount, $campaignId]);
                    $totalClicksFromDB = $actualClickCount;
                }
                
                // Use the synced count
                $totalClicks = max($totalClicksFromDB, $actualClickCount);
                
                echo json_encode([
                    'success' => true,
                    'campaign' => $campaign,
                    'recipients' => $recipients,
                    'recipient_count' => count($recipients),
                    'status_counts' => $statusCounts,
                    'clicks' => $clicks,
                    'click_count' => $totalClicks
                ]);
            } catch (PDOException $e) {
                error_log("Campaign details error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to fetch campaign details: ' . $e->getMessage()]);
            }
            break;
            
        case 'stats':
            $pdo = getDBConnection();
            
            // Get total users
            $totalUsers = 0;
            if ($pdo !== null) {
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'employee'");
                    $result = $stmt->fetch();
                    $totalUsers = $result['total'] ?? 0;
                } catch (PDOException $e) {
                    error_log("Error fetching user stats: " . $e->getMessage());
                }
            }
            
            // Calculate training statistics
            $totalModules = 4; // phishing, password, data, browsing
            $totalCompletions = 0;
            $totalScoreSum = 0;
            $moduleCompletions = [
                'phishing' => 0,
                'password' => 0,
                'data' => 0,
                'browsing' => 0
            ];
            $moduleScores = [
                'phishing' => 0,
                'password' => 0,
                'data' => 0,
                'browsing' => 0
            ];
            $recentActivity = [];
            
            if ($pdo !== null) {
                try {
                    // Get training progress only for employees (exclude admins from training stats)
                    $stmt = $pdo->prepare("
                        SELECT tp.*, u.name as user_name 
                        FROM training_progress tp
                        JOIN users u ON tp.user_id = u.id
                        WHERE u.role = 'employee'
                        ORDER BY tp.completed_at DESC
                    ");
                    $stmt->execute();
                    $allProgress = $stmt->fetchAll();
                    
                    foreach ($allProgress as $progress) {
                        $moduleKey = $progress['module_key'];
                        $score = (int)$progress['score'];
                        
                        $totalCompletions++;
                        $totalScoreSum += $score;
                        
                        // Count module completions (initialize if not exists)
                        if (!isset($moduleCompletions[$moduleKey])) {
                            $moduleCompletions[$moduleKey] = 0;
                            $moduleScores[$moduleKey] = 0;
                        }
                        $moduleCompletions[$moduleKey]++;
                        $moduleScores[$moduleKey] += $score;
                    }
                    
                    // Build recent activity from training completions
                    foreach (array_slice($allProgress, 0, 5) as $progress) {
                        $moduleNames = [
                            'phishing' => 'Phishing Awareness',
                            'password' => 'Password Security',
                            'data' => 'Data Protection',
                            'browsing' => 'Safe Browsing'
                        ];
                        $moduleName = $moduleNames[$progress['module_key']] ?? $progress['module_key'];
                        $recentActivity[] = [
                            'action' => 'Completed Training',
                            'campaign' => $moduleName . ' (' . $progress['score'] . '%)',
                            'timestamp' => $progress['completed_at'],
                            'recipients' => $progress['user_name']
                        ];
                    }
                } catch (PDOException $e) {
                    // If training_progress table doesn't exist yet, use empty data
                    error_log("Error fetching training stats: " . $e->getMessage());
                }
            }
            
            // Calculate rates based on training completion
            // Click Rate = percentage of users who haven't completed all modules (needs training)
            $usersWithAllModules = 0;
            if ($pdo !== null && $totalUsers > 0) {
                try {
                    $stmt = $pdo->prepare("
                        SELECT tp.user_id, COUNT(DISTINCT tp.module_key) as module_count
                        FROM training_progress tp
                        JOIN users u ON tp.user_id = u.id
                        WHERE u.role = 'employee'
                        GROUP BY tp.user_id
                        HAVING module_count = ?
                    ");
                    $stmt->execute([$totalModules]);
                    $usersWithAllModules = count($stmt->fetchAll());
                } catch (PDOException $e) {
                    error_log("Error calculating completion stats: " . $e->getMessage());
                }
            }
            
            $incompleteUsers = max(0, $totalUsers - $usersWithAllModules);
            $clickRate = $totalUsers > 0 ? round(($incompleteUsers / $totalUsers) * 100, 1) : 0;
            
            // Report Rate = percentage of users who have completed at least one module (engagement/awareness)
            $usersWithAnyModule = 0;
            if ($pdo !== null && $totalUsers > 0) {
                try {
                    $stmt = $pdo->prepare("
                        SELECT COUNT(DISTINCT tp.user_id) as count 
                        FROM training_progress tp
                        JOIN users u ON tp.user_id = u.id
                        WHERE u.role = 'employee'
                    ");
                    $stmt->execute();
                    $result = $stmt->fetch();
                    $usersWithAnyModule = $result['count'] ?? 0;
                } catch (PDOException $e) {
                    error_log("Error calculating report stats: " . $e->getMessage());
                }
            }
            $reportRate = $totalUsers > 0 ? round(($usersWithAnyModule / $totalUsers) * 100, 1) : 0;
            
            // Success Rate = percentage of all possible training completions that have been completed
            // This shows overall training completion progress across all users
            // Formula: (actual completions) / (total users × 4 modules) × 100
            $maxPossibleCompletions = $totalUsers * $totalModules;
            $successRate = $maxPossibleCompletions > 0 ? round(($totalCompletions / $maxPossibleCompletions) * 100, 1) : 0;
            
            // Also calculate average score for reference (can be used elsewhere)
            $avgScore = $totalCompletions > 0 ? round($totalScoreSum / $totalCompletions, 1) : 0;
            
            // Calculate department stats (group by user for now, can be enhanced later)
            $departments = [];
            if ($totalUsers > 0) {
                $departments['All Employees'] = $totalUsers;
            }
            
            // For campaigns compatibility, set these values
            $totalCampaigns = 0; // Not using campaigns anymore
            $activeCampaigns = 0;
            $totalRecipients = $totalUsers;
            $totalClicks = $incompleteUsers;
            $totalReports = $usersWithAnyModule;

            $stats = [
                'total_campaigns' => $totalCampaigns,
                'active_campaigns' => $activeCampaigns,
                'total_recipients' => $totalRecipients,
                'total_clicks' => $totalClicks,
                'total_reports' => $totalReports,
                'click_rate' => $clickRate,
                'report_rate' => $reportRate,
                'success_rate' => $successRate,
                'total_users' => $totalUsers,
                'departments' => $departments,
                'recent_activity' => $recentActivity,
                'training_stats' => [
                    'total_completions' => $totalCompletions,
                    'average_score' => $avgScore,
                    'module_completions' => $moduleCompletions,
                    'users_with_all_modules' => $usersWithAllModules
                ]
            ];

            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
    }
}

// Tracking API
function handleTrackingAPI() {
    $campaign_id = $_GET['campaign_id'] ?? '';
    $user_id = $_GET['user_id'] ?? '';
    $action = $_GET['action'] ?? 'click';
    
    // Log tracking data (in real implementation, save to database)
    $tracking_data = [
        'campaign_id' => $campaign_id,
        'user_id' => $user_id,
        'action' => $action,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // In real implementation, save to database
    // For now, just return success
    echo json_encode(['success' => true, 'tracked' => $tracking_data]);
}

// Email API
function handleEmailAPI() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        return;
    }
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $user_email = $_SESSION['user_email'] ?? '';
    $user_name = $_SESSION['user_name'] ?? 'User';
    
    // Get user email from database if not in session
    if (empty($user_email)) {
        $pdo = getDBConnection();
        if ($pdo !== null) {
            try {
                $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                if ($user) {
                    $user_email = $user['email'];
                    $_SESSION['user_email'] = $user_email;
                }
            } catch (PDOException $e) {
                error_log("Error fetching user email: " . $e->getMessage());
            }
        }
    }
    
    switch($action) {
        case 'report_incident':
            $reportDetails = $_POST['details'] ?? '';
            
            if (empty($reportDetails)) {
                http_response_code(400);
                echo json_encode(['error' => 'Report details are required']);
                return;
            }
            
            try {
                $emailHelper = new TrainMeEmail();
                $result = $emailHelper->sendSecurityReport($user_email, $user_name, $reportDetails);
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Security incident reported successfully. You will receive a confirmation email shortly.'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => $result['message']]);
                }
            } catch (Exception $e) {
                error_log("Security report email error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to send security report. Please try again.']);
            }
            break;
            
        case 'ask_question':
            $question = $_POST['question'] ?? '';
            
            if (empty($question)) {
                http_response_code(400);
                echo json_encode(['error' => 'Question is required']);
                return;
            }
            
            try {
                $emailHelper = new TrainMeEmail();
                $result = $emailHelper->sendSecurityQuestion($user_email, $user_name, $question);
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Your question has been sent. You will receive a confirmation email and our team will respond within 24 hours.'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => $result['message']]);
                }
            } catch (Exception $e) {
                error_log("Security question email error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to send question. Please try again.']);
            }
            break;
            
        case 'send':
            $to = $_POST['to'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $content = $_POST['content'] ?? '';
            
            // Simulate email sending
            echo json_encode([
                'success' => true,
                'message' => 'Email sent successfully',
                'to' => $to
            ]);
            break;
            
        case 'templates':
            $templates = [
                [
                    'id' => 1,
                    'name' => 'Microsoft Security Alert',
                    'subject' => 'Urgent: Unusual sign-in activity detected',
                    'type' => 'microsoft'
                ],
                [
                    'id' => 2,
                    'name' => 'PayPal Payment Issue',
                    'subject' => 'Action Required: Payment verification needed',
                    'type' => 'paypal'
                ],
                [
                    'id' => 3,
                    'name' => 'HR Policy Update',
                    'subject' => 'New company policy - immediate action required',
                    'type' => 'hr_notice'
                ]
            ];
            echo json_encode(['success' => true, 'templates' => $templates]);
            break;
    }
}

// Users API
function handleUsersAPI() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        return;
    }
    
    // Only admins can access user data
    if ($_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    $action = $_POST['action'] ?? $_GET['action'] ?? 'list';
    
    switch($action) {
        case 'list':
            $pdo = getDBConnection();
            if ($pdo === null) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                return;
            }
            
            try {
                $stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
                $users = $stmt->fetchAll();
                
                // Format the data
                $formattedUsers = array_map(function($user) {
                    return [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'created_at' => $user['created_at'],
                        'status' => 'Active' // All users are active by default
                    ];
                }, $users);
                
                echo json_encode(['success' => true, 'users' => $formattedUsers]);
            } catch (PDOException $e) {
                error_log("Error fetching users: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to fetch users']);
            }
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'employee';

            if ($id <= 0 || $name === '' || $email === '' || !in_array($role, ['employee', 'admin'], true)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid input']);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid email address']);
                return;
            }

            $pdo = getDBConnection();
            if ($pdo === null) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                return;
            }

            try {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
                $stmt->execute([$name, $email, $role, $id]);
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                error_log("Error updating user: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update user']);
            }
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid user id']);
                return;
            }

            // Prevent deleting yourself to avoid locking out the admin
            if ($id === (int)($_SESSION['user_id'] ?? 0)) {
                http_response_code(400);
                echo json_encode(['error' => 'You cannot delete your own account while logged in']);
                return;
            }

            $pdo = getDBConnection();
            if ($pdo === null) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                return;
            }

            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                error_log("Error deleting user: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete user']);
            }
            break;

        case 'training':
            $userId = (int)($_GET['user_id'] ?? $_POST['user_id'] ?? 0);

            if ($userId <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid user id']);
                return;
            }

            $pdo = getDBConnection();
            if ($pdo === null) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                return;
            }

            try {
                // Get user info
                $stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();

                if (!$user) {
                    http_response_code(404);
                    echo json_encode(['error' => 'User not found']);
                    return;
                }

                // Get training progress for this user
                $stmt = $pdo->prepare("
                    SELECT module_key, score, completed_at, updated_at
                    FROM training_progress
                    WHERE user_id = ?
                    ORDER BY completed_at DESC
                ");
                $stmt->execute([$userId]);
                $trainingProgress = $stmt->fetchAll();

                // Format the data
                $moduleNames = [
                    'phishing' => 'Phishing Awareness',
                    'password' => 'Password Security',
                    'data' => 'Data Protection',
                    'browsing' => 'Safe Browsing'
                ];

                $formattedProgress = [];
                $totalScore = 0;
                $completedCount = 0;

                foreach ($trainingProgress as $progress) {
                    $moduleKey = $progress['module_key'];
                    $score = (int)$progress['score'];
                    $formattedProgress[] = [
                        'module_key' => $moduleKey,
                        'module_name' => $moduleNames[$moduleKey] ?? $moduleKey,
                        'score' => $score,
                        'completed_at' => $progress['completed_at'],
                        'updated_at' => $progress['updated_at']
                    ];
                    $totalScore += $score;
                    $completedCount++;
                }

                // Calculate statistics
                $averageScore = $completedCount > 0 ? round($totalScore / $completedCount, 1) : 0;
                $totalModules = 4;
                $completionRate = round(($completedCount / $totalModules) * 100, 1);

                echo json_encode([
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'created_at' => $user['created_at']
                    ],
                    'training' => [
                        'progress' => $formattedProgress,
                        'statistics' => [
                            'completed_modules' => $completedCount,
                            'total_modules' => $totalModules,
                            'completion_rate' => $completionRate,
                            'average_score' => $averageScore,
                            'total_score' => $totalScore
                        ]
                    ]
                ]);
            } catch (PDOException $e) {
                error_log("Error fetching user training: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to fetch training data']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

// Main page logic
$page_title = "TrainMe - Security Awareness Training Platform";
$current_time = date('Y-m-d H:i:s');

// Check authentication
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['user_role'] ?? 'guest';
$user_name = $_SESSION['user_name'] ?? 'Guest';

// Include the HTML template
include 'template.html';
?>
