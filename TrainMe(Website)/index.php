<?php
// TrainMe Backend - Eyad's Backend Logic
session_start();

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

// API Endpoints for Frontend (Amr)
if (isset($_GET['api'])) {
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
                
                // Auto-login after registration
                $userId = $pdo->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_role'] = $role;
                $_SESSION['user_name'] = $name;
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Account created successfully',
                    'role' => $role,
                    'name' => $name
                ]);
            } catch (PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                http_response_code(500);
                // Return more detailed error for debugging (remove in production)
                echo json_encode([
                    'error' => 'Registration failed: ' . $e->getMessage(),
                    'details' => 'Check server logs for more information'
                ]);
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
            // Try to load campaigns from database (if campaigns table exists)
            $pdo = getDBConnection();
            $campaigns = [];

            if ($pdo !== null) {
                try {
                    $stmt = $pdo->query("SELECT id, name, status, department, recipients, clicks, reports, created_at FROM campaigns ORDER BY created_at DESC");
                    $campaigns = $stmt->fetchAll();
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
            
            $name = $_POST['name'] ?? '';
            $template = $_POST['template'] ?? '';
            $targets = $_POST['targets'] ?? [];
            
            // Simulate campaign creation
            $campaign_id = rand(100, 999);
            echo json_encode([
                'success' => true, 
                'campaign_id' => $campaign_id,
                'message' => 'Campaign created successfully'
            ]);
            break;
            
        case 'send':
            if ($_SESSION['user_role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                return;
            }
            
            $campaign_id = $_POST['campaign_id'] ?? '';
            
            // Simulate email sending
            echo json_encode([
                'success' => true,
                'message' => 'Campaign sent to 50 recipients',
                'sent_count' => 50
            ]);
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
                    // Get all training progress
                    $stmt = $pdo->query("
                        SELECT tp.*, u.name as user_name 
                        FROM training_progress tp
                        JOIN users u ON tp.user_id = u.id
                        ORDER BY tp.completed_at DESC
                    ");
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
            // Click Rate = percentage of users who haven't completed all modules (susceptibility)
            $usersWithAllModules = 0;
            if ($pdo !== null && $totalUsers > 0) {
                try {
                    $stmt = $pdo->query("
                        SELECT user_id, COUNT(DISTINCT module_key) as module_count
                        FROM training_progress
                        GROUP BY user_id
                        HAVING module_count = $totalModules
                    ");
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
                    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as count FROM training_progress");
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
    
    switch($action) {
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
