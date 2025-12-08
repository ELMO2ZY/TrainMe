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
                        <a href="#" class="forgot-password" onclick="event.preventDefault(); showForgotPasswordModal();">Forget your password?</a>
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

        // Forgot Password Modal
        function showForgotPasswordModal() {
            const modal = document.getElementById('forgot-password-modal');
            if (modal) {
                modal.style.display = 'flex';
            }
        }

        function closeForgotPasswordModal() {
            const modal = document.getElementById('forgot-password-modal');
            if (modal) {
                modal.style.display = 'none';
                document.getElementById('forgot-password-form').reset();
                document.getElementById('forgot-password-error').style.display = 'none';
                document.getElementById('forgot-password-success').style.display = 'none';
            }
        }

        async function submitForgotPassword() {
            const email = document.getElementById('forgot-email').value.trim();
            const role = document.querySelector('input[name="forgot-role"]:checked');
            const errorDiv = document.getElementById('forgot-password-error');
            const successDiv = document.getElementById('forgot-password-success');
            const submitBtn = document.getElementById('forgot-password-submit-btn');
            
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';
            
            if (!email || !email.includes('@')) {
                errorDiv.textContent = 'Please enter a valid email address';
                errorDiv.style.display = 'block';
                return;
            }
            
            if (!role) {
                errorDiv.textContent = 'Please select your role (Employee or Admin)';
                errorDiv.style.display = 'block';
                return;
            }
            
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            
            try {
                const formData = new FormData();
                formData.append('action', 'forgot_password');
                formData.append('email', email);
                formData.append('role', role.value);
                
                const response = await fetch('index.php?api=auth', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    successDiv.textContent = data.message || 'If an account with that email exists, a password reset link has been sent to your email.';
                    successDiv.style.display = 'block';
                    document.getElementById('forgot-password-form').reset();
                    setTimeout(() => {
                        closeForgotPasswordModal();
                    }, 3000);
                } else {
                    errorDiv.textContent = data.error || 'Failed to send password reset email';
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                console.error('Forgot password error:', error);
                errorDiv.textContent = 'Failed to send password reset email. Please try again.';
                errorDiv.style.display = 'block';
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    </script>

    <!-- Forgot Password Modal -->
    <div id="forgot-password-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 10000; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);">
        <div style="background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                <h3 style="margin: 0; font-size: 1.5rem; color: #0f172a; font-weight: 700;">Reset Password</h3>
                <button onclick="closeForgotPasswordModal()" style="background: none; border: none; font-size: 1.75rem; color: #6b7280; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: all 0.2s;" onmouseover="this.style.background='#f3f4f6'; this.style.color='#374151'" onmouseout="this.style.background='none'; this.style.color='#6b7280'">&times;</button>
            </div>
            <div style="padding: 1.5rem;">
                <p style="margin: 0 0 1.5rem 0; color: #6b7280; line-height: 1.6;">Enter your email address and role, and we'll send you a link to reset your password.</p>
                
                <form id="forgot-password-form" onsubmit="event.preventDefault(); submitForgotPassword();">
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.5rem;">Email Address <span style="color: #dc2626;">*</span></label>
                        <input type="email" id="forgot-email" required 
                               style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box; transition: all 0.2s; outline: none;"
                               onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)'"
                               onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                               placeholder="your.email@example.com"
                               autocomplete="email">
                    </div>
                    
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.5rem;">Select your role <span style="color: #dc2626;">*</span></label>
                        <div style="display: flex; gap: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="radio" name="forgot-role" value="employee" required>
                                <span>Employee</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="radio" name="forgot-role" value="admin" required>
                                <span>Admin</span>
                            </label>
                        </div>
                    </div>
                    
                    <div id="forgot-password-error" class="error-message" style="display: none; margin-bottom: 1rem;"></div>
                    <div id="forgot-password-success" style="display: none; padding: 1rem; background: #d1fae5; border-radius: 6px; color: #065f46; margin-bottom: 1rem;"></div>
                    
                    <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                        <button type="button" onclick="closeForgotPasswordModal()" style="padding: 0.75rem 1.5rem; border: 1px solid #d1d5db; border-radius: 6px; background: white; color: #374151; font-weight: 500; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">Cancel</button>
                        <button type="submit" id="forgot-password-submit-btn" style="padding: 0.75rem 1.5rem; border: none; border-radius: 6px; background: #2563eb; color: white; font-weight: 500; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Send Reset Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

