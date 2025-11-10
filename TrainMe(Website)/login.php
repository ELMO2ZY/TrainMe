<?php
session_start();

// If already logged in, redirect to main page
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$page_title = "Sign In - TrainMe";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <!-- Left Side: Security Theme -->
        <div class="login-left">
            <div class="security-background">
                <div class="circuit-pattern"></div>
                <div class="security-icon-wrapper">
                    <div class="shield-icon">
                        <svg viewBox="0 0 100 100" class="shield-svg">
                            <path d="M50 10 L20 20 L20 50 Q20 70 35 85 Q50 95 50 95 Q50 95 65 85 Q80 70 80 50 L80 20 Z" 
                                  fill="none" stroke="#00d4ff" stroke-width="2" class="shield-outline"/>
                        </svg>
                        <svg viewBox="0 0 24 24" class="lock-icon">
                            <path d="M12 1C8.1 1 5 4.1 5 8v3c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-8c0-1.1-.9-2-2-2V8c0-3.9-3.1-7-7-7zm0 2c2.8 0 5 2.2 5 5v3H7V8c0-2.8 2.2-5 5-5zm-2 10c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2z" 
                                  fill="#00d4ff" class="lock-path"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="login-right">
            <div class="login-form-wrapper">
                <div style="margin-bottom: 1rem;">
                    <a href="index.php" class="home-button" style="color: #6b7280; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        Home
                    </a>
                </div>
                <h1 class="login-title">sign In</h1>
                
                <!-- Social Login Icons -->
                <div class="social-login">
                    <button class="social-btn google-btn" type="button" aria-label="Sign in with Google">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                    </button>
                    <button class="social-btn facebook-btn" type="button" aria-label="Sign in with Facebook">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </button>
                </div>

                <p class="divider-text">or use your email password</p>

                <!-- Login Form -->
                <form id="loginForm" class="login-form" method="POST" action="index.php?api=auth">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="role" id="selectedRole" value="">
                    
                    <div class="form-group">
                        <input type="email" id="email" name="email" placeholder="Email" required autocomplete="email">
                    </div>
                    
                    <div class="form-group">
                        <input type="password" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                    </div>

                    <!-- Role Selection -->
                    <div class="form-group role-selection">
                        <label class="role-label">Select your role:</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="role" value="employee" required>
                                <span class="radio-custom"></span>
                                <span class="radio-text">Employee</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="role" value="admin" required>
                                <span class="radio-custom"></span>
                                <span class="radio-text">Admin</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-footer">
                        <a href="#" class="forgot-password">Forget your password?</a>
                    </div>

                    <button type="submit" class="signin-btn">Sign IN</button>
                </form>

                <div id="error-message" class="error-message" style="display: none;"></div>

                <div class="signup-link">
                    <p>Don't have an account? <a href="signup.php">Make one</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add visual feedback for radio button selection
        document.querySelectorAll('input[name="role"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.radio-option').forEach(option => {
                    option.classList.remove('selected');
                });
                if (this.checked) {
                    this.closest('.radio-option').classList.add('selected');
                }
            });
        });

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const role = document.querySelector('input[name="role"]:checked');
                const errorMessage = document.getElementById('error-message');
                
                errorMessage.style.display = 'none';
                
                // Validate role selection
                if (!role) {
                    errorMessage.textContent = 'Please select your role (Employee or Admin)';
                    errorMessage.style.display = 'block';
                    return;
                }
                
                // Set the selected role in hidden field
                document.getElementById('selectedRole').value = role.value;
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'login');
                    formData.append('email', email);
                    formData.append('password', password);
                    formData.append('role', role.value);

                    const response = await fetch('index.php?api=auth', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        // Redirect based on selected role
                        const selectedRole = role.value;
                        if (selectedRole === 'admin') {
                            window.location.href = 'admin.php';
                        } else if (selectedRole === 'employee') {
                            window.location.href = 'employee.php';
                        } else {
                            window.location.href = 'index.php';
                        }
                    } else {
                        errorMessage.textContent = data.error || 'Invalid credentials';
                        errorMessage.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    errorMessage.textContent = 'Login failed. Please try again.';
                    errorMessage.style.display = 'block';
                }
        });
    </script>
</body>
</html>

