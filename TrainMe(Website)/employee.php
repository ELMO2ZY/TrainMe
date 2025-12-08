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
$user_email = 'N/A';
$account_created = null;

// Load training progress from database
$progress = [];
if (isset($_SESSION['user_id'])) {
    try {
        $db_config = [
            'host' => 'localhost',
            'dbname' => 'trainme_db',
            'username' => 'root',
            'password' => 'Eyadelmo2zy69'
        ];
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        $stmt = $pdo->prepare("
            SELECT module_key, score, completed_at 
            FROM training_progress 
            WHERE user_id = ? 
            ORDER BY completed_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $results = $stmt->fetchAll();
        
        foreach ($results as $row) {
            $progress[$row['module_key']] = [
                'score' => (int)$row['score'],
                'completed_at' => $row['completed_at']
            ];
        }
        
        // Fetch user email and account creation date
        $userStmt = $pdo->prepare("
            SELECT email, created_at 
            FROM users 
            WHERE id = ?
        ");
        $userStmt->execute([$_SESSION['user_id']]);
        $userData = $userStmt->fetch();
        $user_email = $userData['email'] ?? 'N/A';
        $account_created = $userData['created_at'] ?? null;
        
        // Store in session for future use
        $_SESSION['training_progress'] = $progress;
    } catch (PDOException $e) {
        error_log("Error loading training progress: " . $e->getMessage());
        // Fallback to session data if database fails
        $progress = $_SESSION['training_progress'] ?? [];
    }
}

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
                <h2>Security Resources</h2>
                <p>Essential tools, guides, and information to help you stay secure.</p>
            </div>
            
            <div class="resource-grid">
                <!-- Quick Actions -->
                <article class="resource-card">
                    <div class="resource-icon">🚨</div>
                    <div class="resource-tag">URGENT</div>
                    <h3>Report Security Incident</h3>
                    <p>If you've clicked a suspicious link, received a phishing email, or suspect a security breach, report it immediately.</p>
                    <div class="resource-actions">
                        <button class="link-btn" onclick="showReportModal()">Report Now →</button>
                    </div>
                </article>

                <article class="resource-card">
                    <div class="resource-icon">📋</div>
                    <div class="resource-tag">QUICK REFERENCE</div>
                    <h3>Phishing Detection Checklist</h3>
                    <p>Use this interactive checklist to verify emails before clicking links or opening attachments.</p>
                    <div class="resource-actions">
                        <button class="link-btn" onclick="window.location.href='employee_checklist.php'">Open Checklist →</button>
                    </div>
                </article>

                <article class="resource-card">
                    <div class="resource-icon">🔐</div>
                    <div class="resource-tag">BEST PRACTICES</div>
                    <h3>Password Security Guide</h3>
                    <p>Learn how to create strong passwords, use password managers, and enable two-factor authentication.</p>
                    <div class="resource-actions">
                        <button class="link-btn" onclick="goToModule('password')">View Guide →</button>
                    </div>
                </article>

                <!-- Security Tips -->
                <article class="resource-card">
                    <div class="resource-icon">⚠️</div>
                    <div class="resource-tag">RED FLAGS</div>
                    <h3>Common Phishing Indicators</h3>
                    <p>Recognize warning signs: urgent requests, suspicious sender addresses, unexpected attachments, and requests for credentials.</p>
                    <div class="resource-actions">
                        <button class="link-btn" onclick="goToModule('phishing')">Learn More →</button>
                    </div>
                </article>

                <article class="resource-card">
                    <div class="resource-icon">💬</div>
                    <div class="resource-tag">SUPPORT</div>
                    <h3>Get Security Help</h3>
                    <p>Have a security question or need clarification? Our security team is here to help you make informed decisions.</p>
                    <div class="resource-actions">
                        <button class="link-btn" onclick="showQuestionModal()">Contact Security →</button>
                    </div>
                </article>

                <article class="resource-card">
                    <div class="resource-icon">📚</div>
                    <div class="resource-tag">TRAINING</div>
                    <h3>Review Training Modules</h3>
                    <p>Access all security training modules to refresh your knowledge and improve your security awareness score.</p>
                    <div class="resource-actions">
                        <button class="link-btn" onclick="goToTrainingPage()">View All Modules →</button>
                    </div>
                </article>

                <!-- Data Protection -->
                <article class="resource-card">
                    <div class="resource-icon">🛡️</div>
                    <div class="resource-tag">DATA PROTECTION</div>
                    <h3>Data Handling Guidelines</h3>
                    <p>Learn how to properly handle, store, and share company and customer data to prevent breaches.</p>
                    <div class="resource-actions">
                        <button class="link-btn" onclick="goToModule('data')">View Guidelines →</button>
                    </div>
                </article>

                <article class="resource-card">
                    <div class="resource-icon">🌐</div>
                    <div class="resource-tag">SAFE BROWSING</div>
                    <h3>Secure Browsing Practices</h3>
                    <p>Protect yourself from malicious websites, popups, and downloads while browsing the internet.</p>
                    <div class="resource-actions">
                        <button class="link-btn" onclick="goToModule('browsing')">Learn Practices →</button>
                    </div>
                </article>

                <article class="resource-card">
                    <div class="resource-icon">📊</div>
                    <div class="resource-tag">YOUR PROGRESS</div>
                    <h3>Training Progress</h3>
                    <p>Track your security training completion, scores, and identify areas where you need improvement.</p>
                    <div class="resource-actions">
                        <button class="link-btn" onclick="showEmployeeSection('overview')">View Progress →</button>
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
                            <p><strong>Email:</strong> <span id="user-email"><?php echo htmlspecialchars($user_email); ?></span></p>
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
                            <span>Account created</span>
                            <span>
                                <?php
                                if ($account_created) {
                                    echo date('M d, Y', strtotime($account_created));
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </span>
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
                        <?php if ($average_score > 0): ?>
                        <div class="profile-meta-row">
                            <span>Average score</span>
                            <span style="color: <?php echo $average_score >= 90 ? '#059669' : ($average_score >= 70 ? '#d97706' : '#dc2626'); ?>; font-weight: 600;">
                                <?php echo $average_score; ?>%
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Security Report Modal -->
    <div id="reportModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeReportModal()">&times;</span>
            <h2>Report Security Incident</h2>
            <form id="reportForm">
                <div class="form-group">
                    <label for="reportDetails">Incident Details:</label>
                    <textarea id="reportDetails" name="details" rows="6" required placeholder="Please describe the security incident in detail. Include what happened, when it occurred, and any relevant information..."></textarea>
                </div>
                <div id="reportMessage" style="display: none; margin: 1rem 0; padding: 1rem; border-radius: 5px;"></div>
                <button type="submit" class="btn btn-primary">Submit Report</button>
                <button type="button" class="btn btn-outline" onclick="closeReportModal()" style="margin-left: 1rem;">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Security Question Modal -->
    <div id="questionModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeQuestionModal()">&times;</span>
            <h2>Ask Security Question</h2>
            <form id="questionForm">
                <div class="form-group">
                    <label for="questionText">Your Question:</label>
                    <textarea id="questionText" name="question" rows="6" required placeholder="Please describe your security question or concern..."></textarea>
                </div>
                <div id="questionMessage" style="display: none; margin: 1rem 0; padding: 1rem; border-radius: 5px;"></div>
                <button type="submit" class="btn btn-primary">Send Question</button>
                <button type="button" class="btn btn-outline" onclick="closeQuestionModal()" style="margin-left: 1rem;">Cancel</button>
            </form>
        </div>
    </div>

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

        // Navigate to specific training module
        function goToModule(key) {
            if (key === 'phishing') window.location.href = 'training_phishing.php';
            if (key === 'password') window.location.href = 'training_password.php';
            if (key === 'data') window.location.href = 'training_data.php';
            if (key === 'browsing') window.location.href = 'training_browsing.php';
        }

        // Show report modal
        function showReportModal() {
            document.getElementById('reportModal').style.display = 'flex';
            document.getElementById('reportForm').reset();
            document.getElementById('reportMessage').style.display = 'none';
        }

        // Close report modal
        function closeReportModal() {
            document.getElementById('reportModal').style.display = 'none';
        }

        // Show question modal
        function showQuestionModal() {
            document.getElementById('questionModal').style.display = 'flex';
            document.getElementById('questionForm').reset();
            document.getElementById('questionMessage').style.display = 'none';
        }

        // Close question modal
        function closeQuestionModal() {
            document.getElementById('questionModal').style.display = 'none';
        }

        // Handle report form submission
        document.getElementById('reportForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const details = document.getElementById('reportDetails').value;
            const messageDiv = document.getElementById('reportMessage');

            if (!details.trim()) {
                messageDiv.style.display = 'block';
                messageDiv.style.background = '#fee2e2';
                messageDiv.style.color = '#991b1b';
                messageDiv.textContent = 'Please provide incident details.';
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'report_incident');
                formData.append('details', details);

                const response = await fetch('index.php?api=email', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    messageDiv.style.display = 'block';
                    messageDiv.style.background = '#d1fae5';
                    messageDiv.style.color = '#065f46';
                    messageDiv.textContent = data.message || 'Report submitted successfully!';
                    document.getElementById('reportForm').reset();
                    
                    setTimeout(() => {
                        closeReportModal();
                    }, 2000);
                } else {
                    messageDiv.style.display = 'block';
                    messageDiv.style.background = '#fee2e2';
                    messageDiv.style.color = '#991b1b';
                    messageDiv.textContent = data.error || 'Failed to submit report. Please try again.';
                }
            } catch (error) {
                console.error('Report error:', error);
                messageDiv.style.display = 'block';
                messageDiv.style.background = '#fee2e2';
                messageDiv.style.color = '#991b1b';
                messageDiv.textContent = 'An error occurred. Please try again.';
            }
        });

        // Handle question form submission
        document.getElementById('questionForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const question = document.getElementById('questionText').value;
            const messageDiv = document.getElementById('questionMessage');

            if (!question.trim()) {
                messageDiv.style.display = 'block';
                messageDiv.style.background = '#fee2e2';
                messageDiv.style.color = '#991b1b';
                messageDiv.textContent = 'Please enter your question.';
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'ask_question');
                formData.append('question', question);

                const response = await fetch('index.php?api=email', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    messageDiv.style.display = 'block';
                    messageDiv.style.background = '#d1fae5';
                    messageDiv.style.color = '#065f46';
                    messageDiv.textContent = data.message || 'Question sent successfully!';
                    document.getElementById('questionForm').reset();
                    
                    setTimeout(() => {
                        closeQuestionModal();
                    }, 2000);
                } else {
                    messageDiv.style.display = 'block';
                    messageDiv.style.background = '#fee2e2';
                    messageDiv.style.color = '#991b1b';
                    messageDiv.textContent = data.error || 'Failed to send question. Please try again.';
                }
            } catch (error) {
                console.error('Question error:', error);
                messageDiv.style.display = 'block';
                messageDiv.style.background = '#fee2e2';
                messageDiv.style.color = '#991b1b';
                messageDiv.textContent = 'An error occurred. Please try again.';
            }
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            const reportModal = document.getElementById('reportModal');
            const questionModal = document.getElementById('questionModal');
            if (e.target === reportModal) {
                closeReportModal();
            }
            if (e.target === questionModal) {
                closeQuestionModal();
            }
        });

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
