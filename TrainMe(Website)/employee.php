<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user is an employee (optional - you can remove this if you want to allow any logged-in user)
// if ($_SESSION['user_role'] !== 'employee') {
//     header('Location: index.php');
//     exit;
// }

$page_title = "Employee Dashboard - TrainMe";
$user_name = $_SESSION['user_name'] ?? 'Employee';
$user_role = $_SESSION['user_role'] ?? 'employee';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <div class="logo">
                <div class="logo-icon">T</div>
                TrainMe
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="#training">Training</a></li>
                    <li><a href="#profile">Profile</a></li>
                </ul>
            </nav>
            <div class="nav-buttons">
                <span style="margin-right: 1rem; color: #374151;">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
                <button onclick="logout()" class="btn btn-outline">Logout</button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-container">
                <div class="hero-content">
                    <h1>Welcome, <span class="highlight"><?php echo htmlspecialchars($user_name); ?></span></h1>
                    <p>Complete your security awareness training and stay protected from phishing attacks.</p>
                </div>
            </div>
        </section>

        <!-- Training Section -->
        <section id="training" class="training">
            <div class="training-container">
                <h2>Security Training Modules</h2>
                <p>Enhance your security awareness with our interactive training modules.</p>
                
                <div class="training-modules">
                    <div class="training-card">
                        <div class="feature-icon">📧</div>
                        <h3>Phishing Awareness</h3>
                        <p>Learn to identify and avoid phishing attacks. Recognize suspicious emails and protect yourself from cyber threats.</p>
                        <button class="btn btn-primary" onclick="startTraining('phishing')">Start Training</button>
                    </div>
                    
                    <div class="training-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Password Security</h3>
                        <p>Best practices for creating and managing secure passwords. Learn about password managers and two-factor authentication.</p>
                        <button class="btn btn-primary" onclick="startTraining('password')">Start Training</button>
                    </div>
                    
                    <div class="training-card">
                        <div class="feature-icon">🛡️</div>
                        <h3>Data Protection</h3>
                        <p>Understand how to protect sensitive data and recognize social engineering attempts.</p>
                        <button class="btn btn-primary" onclick="startTraining('data')">Start Training</button>
                    </div>
                    
                    <div class="training-card">
                        <div class="feature-icon">💻</div>
                        <h3>Safe Browsing</h3>
                        <p>Learn about safe internet practices, recognizing malicious websites, and protecting your devices.</p>
                        <button class="btn btn-primary" onclick="startTraining('browsing')">Start Training</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Profile Section -->
        <section id="profile" class="profile">
            <div class="profile-container">
                <h2>My Profile</h2>
                <div class="profile-info">
                    <p><strong>Name:</strong> <span id="user-name"><?php echo htmlspecialchars($user_name); ?></span></p>
                    <p><strong>Role:</strong> <span id="user-role"><?php echo htmlspecialchars($user_role); ?></span></p>
                    <p><strong>User ID:</strong> <span id="user-id"><?php echo htmlspecialchars($_SESSION['user_id']); ?></span></p>
                    <p><strong>Status:</strong> <span style="color: #059669; font-weight: 600;">Active</span></p>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Logout function
        async function logout() {
            try {
                const response = await fetch('index.php?api=auth&action=logout', {
                    method: 'POST'
                });
                
                const data = await response.json();
                if (data.success) {
                    window.location.href = 'index.php';
                }
            } catch (error) {
                console.error('Logout error:', error);
                window.location.href = 'index.php';
            }
        }

        // Start training function
        function startTraining(type) {
            alert(`Starting ${type} training module. This would open the training content.`);
            // In a real implementation, this would redirect to the training module
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>

