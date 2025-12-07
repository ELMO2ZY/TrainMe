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
$last_login = $_SESSION['last_login'] ?? null;

// Get training progress
$progress = $_SESSION['training_progress'] ?? [];

// Define all available training modules
$all_modules = [
    'phishing' => 'Phishing Awareness',
    'password' => 'Password Security',
    'data' => 'Data Protection',
    'browsing' => 'Safe Browsing'
];

// Calculate overall progress
$total_modules = count($all_modules);
$completed_modules = 0;
$total_score = 0;
$completed_module_names = [];
$incomplete_modules = [];

foreach ($all_modules as $key => $name) {
    if (isset($progress[$key]) && isset($progress[$key]['score'])) {
        $completed_modules++;
        $total_score += $progress[$key]['score'];
        $completed_module_names[] = $name;
    } else {
        $incomplete_modules[$key] = $name;
    }
}

$overall_percentage = $total_modules > 0 ? round(($completed_modules / $total_modules) * 100) : 0;
$average_score = $completed_modules > 0 ? round($total_score / $completed_modules) : 0;

// Find weakest module (lowest score)
$weakest_module_key = null;
$weakest_module_score = 100;
$weakest_module_name = null;
foreach ($all_modules as $key => $name) {
    if (isset($progress[$key]) && isset($progress[$key]['score'])) {
        $module_score = $progress[$key]['score'];
        if ($module_score < $weakest_module_score) {
            $weakest_module_score = $module_score;
            $weakest_module_key = $key;
            $weakest_module_name = $name;
        }
    }
}

// Determine next action with actionable link
$next_action = "Open a module to begin.";
$next_action_url = "training.php";
$next_module_key = null;
if (count($incomplete_modules) > 0) {
    $next_module_key = array_key_first($incomplete_modules);
    $next_module_name = $incomplete_modules[$next_module_key];
    $next_action = "Start " . $next_module_name . " training.";
    // Map module keys to their training pages
    $module_urls = [
        'phishing' => 'training_phishing.php',
        'password' => 'training_password.php',
        'data' => 'training_data.php',
        'browsing' => 'training_browsing.php'
    ];
    $next_action_url = $module_urls[$next_module_key] ?? 'training.php';
} elseif ($completed_modules > 0 && $average_score < 100 && $weakest_module_key) {
    $next_action = "Retake " . $weakest_module_name . " (score: " . $weakest_module_score . "%) to improve.";
    $module_urls = [
        'phishing' => 'training_phishing.php',
        'password' => 'training_password.php',
        'data' => 'training_data.php',
        'browsing' => 'training_browsing.php'
    ];
    $next_action_url = $module_urls[$weakest_module_key] ?? 'training.php';
} elseif ($completed_modules === $total_modules && $average_score < 100 && $weakest_module_key) {
    $next_action = "Retake " . $weakest_module_name . " (score: " . $weakest_module_score . "%) to improve.";
    $module_urls = [
        'phishing' => 'training_phishing.php',
        'password' => 'training_password.php',
        'data' => 'training_data.php',
        'browsing' => 'training_browsing.php'
    ];
    $next_action_url = $module_urls[$weakest_module_key] ?? 'training.php';
} elseif ($completed_modules === $total_modules && $average_score >= 100) {
    $next_action = "All modules completed with perfect scores! Excellent work!";
    $next_action_url = "training.php";
}

// Dynamic tips based on progress and performance
$security_tips = [
    "Report anything that feels suspicious.",
    "Always verify sender email addresses before clicking links.",
    "Use unique, strong passwords for each account.",
    "Enable two-factor authentication whenever possible.",
    "Never share your password with anyone, even IT support.",
    "Check URLs carefully - look for HTTPS and correct domain names.",
    "Be cautious with email attachments from unknown senders.",
    "Keep your software and browsers up to date.",
    "Use a password manager to generate and store strong passwords.",
    "Verify requests for sensitive information through a separate channel.",
    "Don't use public Wi-Fi for sensitive transactions.",
    "Lock your device when stepping away from your desk."
];

// Select tip based on context
$tip = $security_tips[0]; // Default tip
if ($weakest_module_key) {
    // Provide tip related to weakest area
    $module_tips = [
        'phishing' => "Always verify sender email addresses before clicking links.",
        'password' => "Use unique, strong passwords and enable two-factor authentication.",
        'data' => "Never share sensitive data unless you've verified the recipient's identity.",
        'browsing' => "Check URLs carefully and avoid suspicious websites."
    ];
    $tip = $module_tips[$weakest_module_key] ?? $security_tips[0];
} elseif ($completed_modules === 0) {
    $tip = "Start with Phishing Awareness - it's the most common security threat.";
} else {
    // Rotate tip based on current date to show variety
    $tip_index = (intval(date('j')) + $completed_modules) % count($security_tips);
    $tip = $security_tips[$tip_index];
}

// Training status text
$training_status = "No training started yet.";
if ($completed_modules > 0) {
    $training_status = $completed_modules . " of " . $total_modules . " modules completed";
    if ($average_score > 0) {
        $training_status .= " • Average score: " . $average_score . "%";
    }
}
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
                    <li><a href="#overview" data-section="overview">Overview</a></li>
                    <li><a href="#training" data-section="training">Training</a></li>
                    <li><a href="#resources" data-section="resources">Resources</a></li>
                    <li><a href="#profile" data-section="profile">Profile</a></li>
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
        <section class="employee-hero">
            <div class="employee-hero-content">
                <p class="eyebrow">Employee Dashboard</p>
                <h1>Welcome back, <span class="highlight"><?php echo htmlspecialchars($user_name); ?></span></h1>
                <p>Stay sharp against phishing and keep your team safe with bite-sized, interactive lessons.</p>

                <div class="hero-stats">
                    <div>
                        <span class="stat-label">Training status</span>
                        <p class="stat-value"><?php echo htmlspecialchars($training_status); ?></p>
                    </div>
                    <div>
                        <span class="stat-label">Next action</span>
                        <?php if ($next_action_url && $next_action_url !== 'training.php' && strpos($next_action, 'All modules completed with perfect') === false): ?>
                            <p class="stat-value">
                                <a href="<?php echo htmlspecialchars($next_action_url); ?>" class="action-link">
                                    <?php echo htmlspecialchars($next_action); ?>
                                </a>
                            </p>
                        <?php elseif ($next_action_url === 'training.php' && strpos($next_action, 'Open a module') !== false): ?>
                            <p class="stat-value">
                                <a href="training.php" class="action-link">
                                    <?php echo htmlspecialchars($next_action); ?>
                                </a>
                            </p>
                        <?php else: ?>
                            <p class="stat-value"><?php echo htmlspecialchars($next_action); ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="stat-label">Tip</span>
                        <p class="stat-value success"><?php echo htmlspecialchars($tip); ?></p>
                    </div>
                </div>
            </div>
            <div class="employee-hero-visual">
                    <div class="hero-progress">
                        <div class="progress-header">
                            <span>Overall Progress</span>
                            <span><?php echo $overall_percentage; ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $overall_percentage; ?>%;"></div>
                        </div>
                        <p>
                            <?php if ($completed_modules > 0): ?>
                                <?php echo $completed_modules; ?> of <?php echo $total_modules; ?> modules completed
                                <?php if ($average_score > 0): ?>
                                    • Average score: <?php echo $average_score; ?>%
                                <?php endif; ?>
                            <?php else: ?>
                                Start your first training module to track progress.
                            <?php endif; ?>
                        </p>
                    </div>
            </div>
        </section>

        <!-- Overview Section -->
        <section id="overview" class="employee-overview">
            <div class="section-heading">
                <h2>Your security snapshot</h2>
                <p>Track where you stand and what to do next.</p>
            </div>
            <div class="overview-grid">
                <div class="overview-card">
                    <div class="overview-icon">🎯</div>
                    <h3>Current mission</h3>
                    <?php if ($next_module_key): ?>
                        <p>Complete <strong><?php echo htmlspecialchars($incomplete_modules[$next_module_key]); ?></strong> training module.</p>
                    <?php elseif ($completed_modules === $total_modules): ?>
                        <p>All training modules completed! Keep up the great work.</p>
                    <?php else: ?>
                        <p>Start your security training journey.</p>
                    <?php endif; ?>
                    <button class="link-btn" onclick="goToTrainingPage()">Open training area →</button>
                </div>
                <div class="overview-card">
                    <div class="overview-icon">⏱️</div>
                    <h3>Completion rate</h3>
                    <?php if ($completed_modules > 0): ?>
                        <p><strong><?php echo $overall_percentage; ?>%</strong> of all modules completed.</p>
                        <small><?php echo $completed_modules; ?> out of <?php echo $total_modules; ?> modules finished</small>
                    <?php else: ?>
                        <p>No modules completed yet.</p>
                        <small>Start your first training to see progress</small>
                    <?php endif; ?>
                </div>
                <div class="overview-card">
                    <div class="overview-icon">🏅</div>
                    <h3>Your performance</h3>
                    <?php if ($average_score > 0): ?>
                        <p>Average score: <strong><?php echo $average_score; ?>%</strong></p>
                        <small>
                            <?php if ($average_score >= 90): ?>
                                Excellent work! Keep it up!
                            <?php elseif ($average_score >= 70): ?>
                                Good progress! Review modules to improve.
                            <?php else: ?>
                                Keep practicing to improve your scores.
                            <?php endif; ?>
                        </small>
                    <?php else: ?>
                        <p>Complete a module to see your performance.</p>
                        <small>Your scores will appear here</small>
                    <?php endif; ?>
                </div>
            </div>

        </section>

        <!-- Training Section -->
        <section id="training" class="training">
            <div class="training-container">
                <div class="section-heading">
                    <h2>Ready to elevate your progress?</h2>
                    <p>Deepen your security skills with focused, interactive training paths.</p>
                </div>
                
                <div style="max-width: 640px; margin: 0 auto; text-align: center;">
                    <p style="font-size: 1.05rem; color: #4b5563; margin-bottom: 1.5rem;">
                        Your personalized training space brings all security modules into a single focused view.
                        Jump in now and build confident, phishing‑resistant habits.
                    </p>
                    <button class="btn btn-primary" onclick="goToTrainingPage()" style="font-size: 1.05rem; padding: 0.9rem 2rem;">
                        Go to Training
                    </button>
                </div>
            </div>
        </section>

        <!-- Resources Section -->
        <section id="resources" class="employee-resources">
            <div class="section-heading">
                <h2>Helpful shortcuts</h2>
                <p>Everything you need to stay ahead of attackers.</p>
            </div>
            <div class="resource-grid">
                <article class="resource-card">
                    <div class="resource-tag">High priority</div>
                    <h3>Report a suspicious email</h3>
                    <p>Forward the message to the security team so they can investigate and warn others.</p>
                    <div class="resource-actions">
                        <a class="link-btn" href="mailto:security@trainme.com?subject=Suspicious%20email%20report&body=Hi%20Security%20Team,%0D%0A%0D%0AI%20received%20a%20suspicious%20email.%20Details%20below:%0D%0A">
                            Email security team →
                        </a>
                    </div>
                </article>
                <article class="resource-card">
                    <div class="resource-tag">Guided tips</div>
                    <h3>Open the phishing checklist</h3>
                    <p>Walk through a quick checklist before clicking links or opening attachments.</p>
                    <div class="resource-actions">
                        <button class="link-btn" onclick="window.location.href='employee_checklist.php'">View checklist →</button>
                    </div>
                </article>
                <article class="resource-card">
                    <div class="resource-tag">Need guidance?</div>
                    <h3>Ask for help</h3>
                    <p>Not sure about something? Reach out to security for a quick second opinion.</p>
                    <div class="resource-actions">
                        <a class="link-btn" href="mailto:security@trainme.com?subject=Security%20question">
                            Ask a question →
                        </a>
                    </div>
                </article>
            </div>
        </section>

        <!-- Profile Section -->
        <section id="profile" class="profile">
            <div class="profile-container">
                <div class="section-heading">
                    <h2>My Profile</h2>
                    <p>Keep your account details up to date.</p>
                </div>
                <div class="profile-info">
                    <div class="profile-card-main">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                        </div>
                        <div class="profile-details">
                            <p><strong>Name:</strong> <span id="user-name"><?php echo htmlspecialchars($user_name); ?></span></p>
                            <p><strong>Role:</strong> <span id="user-role"><?php echo htmlspecialchars($user_role); ?></span></p>
                            <p><strong>Status:</strong> <span style="color: #059669; font-weight: 600;">Active</span></p>
                        </div>
                    </div>
                    <div class="profile-meta">
                        <div class="profile-meta-row">
                            <span>User ID</span>
                            <span id="user-id"><?php echo htmlspecialchars($_SESSION['user_id']); ?></span>
                        </div>
                        <div class="profile-meta-row">
                            <span>Last login</span>
                            <span>
                                <?php
                                if ($last_login) {
                                    echo htmlspecialchars($last_login);
                                } else {
                                    echo 'This session (timestamp not recorded yet)';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="profile-meta-row">
                            <span>Training modules completed</span>
                            <span><?php echo $completed_modules; ?> of <?php echo $total_modules; ?></span>
                        </div>
                    </div>
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

        // Redirect to full training page
        function goToTrainingPage() {
            window.location.href = 'training.php';
        }

        // Simple tab-style navigation for employee sections
        function showEmployeeSection(sectionId) {
            const sectionIds = ['overview', 'training', 'resources', 'profile'];
            sectionIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.style.display = id === sectionId ? 'block' : 'none';
                }
            });

            // Update active link styling
            document.querySelectorAll('.nav-links a[data-section]').forEach(link => {
                const isActive = link.getAttribute('data-section') === sectionId;
                link.classList.toggle('active', isActive);
            });

            // Scroll chosen section into view below the hero
            const target = document.getElementById(sectionId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Only show overview by default
            showEmployeeSection('overview');

            // Wire up nav links
            document.querySelectorAll('.nav-links a[data-section]').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const sectionId = this.getAttribute('data-section');
                    showEmployeeSection(sectionId);
                });
            });
        });
    </script>
</body>
</html>
