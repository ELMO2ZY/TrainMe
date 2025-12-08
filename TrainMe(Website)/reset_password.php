<?php
session_start();

// Don't redirect if user is logged in - they might be resetting password while logged in
// We'll handle the session after password reset

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$showForm = false;

if (empty($token)) {
    $error = 'Invalid reset link. Please request a new password reset.';
} else {
    // Get database connection - use the same config as index.php
    // Don't include full index.php as it might have redirect logic or API handling
    $backend_config = [
        'database' => [
            'host' => 'localhost',
            'dbname' => 'trainme_db',
            'username' => 'root',
            'password' => 'Eyadelmo2zy69'
        ]
    ];
    
    if (!function_exists('getDBConnection')) {
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
    }
    
    $pdo = getDBConnection();
    
    if ($pdo !== null) {
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
            
            // First, try to find the token (without JOIN to see if token exists at all)
            $stmt = $pdo->prepare("SELECT user_id, email, expires_at, used FROM password_reset_tokens WHERE token = ?");
            $stmt->execute([$token]);
            $tokenRow = $stmt->fetch();
            
            if (!$tokenRow) {
                $error = 'Invalid reset link. The token was not found. Please request a new password reset.';
            } elseif ($tokenRow['used'] == 1) {
                $error = 'This reset link has already been used. Please request a new password reset.';
            } elseif (strtotime($tokenRow['expires_at']) < time()) {
                $error = 'This reset link has expired. Please request a new password reset.';
            } else {
                // Token is valid, now get user info
                $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
                $stmt->execute([$tokenRow['user_id']]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $showForm = true;
                } else {
                    $error = 'User account not found. Please request a new password reset.';
                }
            }
        } catch (PDOException $e) {
            error_log("Password reset token verification error: " . $e->getMessage());
            error_log("Token being checked: " . substr($token, 0, 20) . "...");
            $error = 'An error occurred while verifying the reset link: ' . $e->getMessage();
        }
    } else {
        $error = 'Database connection failed. Please try again later.';
    }
}

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please enter and confirm your new password.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Reset password via API
        $formData = new FormData();
        $formData->append('action', 'reset_password');
        $formData->append('token', $token);
        $formData->append('password', $newPassword);
        
        // We'll use JavaScript to handle this
    }
}

$page_title = "Reset Password - TrainMe";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="css/login.css">
    <style>
        .reset-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            padding: 2rem;
        }
        .reset-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            padding: 2.5rem;
        }
        .reset-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .reset-subtitle {
            color: #6b7280;
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            box-sizing: border-box;
            transition: all 0.2s;
            outline: none;
        }
        .form-group input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            border-left: 4px solid #dc2626;
        }
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            border-left: 4px solid #059669;
        }
        .reset-btn {
            width: 100%;
            padding: 0.75rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .reset-btn:hover {
            background: #1d4ed8;
        }
        .reset-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-link a {
            color: #2563eb;
            text-decoration: none;
            font-size: 0.875rem;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <h1 class="reset-title">Reset Your Password</h1>
            <p class="reset-subtitle">Enter your new password below</p>
            
            <?php if ($error && !$showForm): ?>
                <div class="error-message">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
                <div class="back-link">
                    <a href="login.php">← Back to Login</a>
                </div>
            <?php elseif ($showForm): ?>
                <div id="error-message" class="error-message" style="display: none;"></div>
                <div id="success-message" class="success-message" style="display: none;"></div>
                
                <form id="reset-password-form" onsubmit="event.preventDefault(); submitResetPassword();">
                    <div class="form-group">
                        <label for="password">New Password <span style="color: #dc2626;">*</span></label>
                        <input type="password" id="password" name="password" required 
                               minlength="6" placeholder="Enter new password"
                               autocomplete="new-password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password <span style="color: #dc2626;">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               minlength="6" placeholder="Confirm new password"
                               autocomplete="new-password">
                    </div>
                    
                    <button type="submit" id="reset-submit-btn" class="reset-btn">Reset Password</button>
                </form>
                
                <div class="back-link">
                    <a href="login.php">← Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const token = '<?php echo htmlspecialchars($token, ENT_QUOTES); ?>';
        
        async function submitResetPassword() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errorDiv = document.getElementById('error-message');
            const successDiv = document.getElementById('success-message');
            const submitBtn = document.getElementById('reset-submit-btn');
            
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';
            
            if (password.length < 6) {
                errorDiv.textContent = 'Password must be at least 6 characters long.';
                errorDiv.style.display = 'block';
                return;
            }
            
            if (password !== confirmPassword) {
                errorDiv.textContent = 'Passwords do not match.';
                errorDiv.style.display = 'block';
                return;
            }
            
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Resetting...';
            
            try {
                const formData = new FormData();
                formData.append('action', 'reset_password');
                formData.append('token', token);
                formData.append('password', password);
                
                const response = await fetch('index.php?api=auth', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    successDiv.textContent = data.message || 'Password has been reset successfully! Redirecting to login...';
                    successDiv.style.display = 'block';
                    document.getElementById('reset-password-form').style.display = 'none';
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    errorDiv.textContent = data.error || 'Failed to reset password. Please try again.';
                    errorDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            } catch (error) {
                console.error('Reset password error:', error);
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    </script>
</body>
</html>

