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

