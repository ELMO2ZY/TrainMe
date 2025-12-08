<?php
session_start();

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = "Training Modules - TrainMe";
$user_name = $_SESSION['user_name'] ?? 'Employee';

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
        
        // Store in session for future use
        $_SESSION['training_progress'] = $progress;
    } catch (PDOException $e) {
        error_log("Error loading training progress: " . $e->getMessage());
        // Fallback to session data if database fails
        $progress = $_SESSION['training_progress'] ?? [];
    }
}

function renderModuleStatus($key, $progress) {
    if (!isset($progress[$key])) {
        return ['text' => 'Not started', 'score' => null];
    }
    $entry = $progress[$key];
    $score = (int)($entry['score'] ?? 0);
    return ['text' => 'Completed • Score: ' . $score . '%', 'score' => $score];
}

function getScoreClass($score) {
    if ($score === null) {
        return '';
    }
    if ($score === 0) {
        return 'score-0';
    } elseif ($score === 100) {
        return 'score-100';
    } else {
        return 'score-other';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="main-content training-page">
        <section class="training">
            <div class="training-container">
                <div class="training-page-top">
                    <button class="btn btn-outline back-button" onclick="goBackToDashboard()">← Back to dashboard</button>
                    <div class="training-page-heading">
                        <h2>Security Training Modules</h2>
                        <p>Choose a module and learn at your own pace.</p>
                        <p class="training-subtext">Pick any card to see more details and track which module you’re focusing on.</p>
                    </div>
                </div>

                <div class="training-modules">
                    <?php 
                    $phishingStatus = renderModuleStatus('phishing', $progress);
                    $passwordStatus = renderModuleStatus('password', $progress);
                    $dataStatus = renderModuleStatus('data', $progress);
                    $browsingStatus = renderModuleStatus('browsing', $progress);
                    ?>
                    <div class="training-card <?php echo isset($progress['phishing']) ? 'completed' : ''; ?>">
                        <div class="feature-icon">📧</div>
                        <h3>Phishing Awareness</h3>
                        <p>Spot suspicious links, spoofed domains, and urgent requests before they cause damage.</p>
                        <div class="module-meta <?php echo getScoreClass($phishingStatus['score']); ?>">
                            <span><?php echo htmlspecialchars($phishingStatus['text']); ?></span>
                        </div>
                        <button class="btn btn-primary" onclick="goToModule('phishing')">
                            <?php echo isset($progress['phishing']) ? 'Review module' : 'Open module'; ?>
                        </button>
                    </div>

                    <div class="training-card <?php echo isset($progress['password']) ? 'completed' : ''; ?>">
                        <div class="feature-icon">🔒</div>
                        <h3>Password Security</h3>
                        <p>Craft unbreakable passwords and master password managers plus MFA.</p>
                        <div class="module-meta <?php echo getScoreClass($passwordStatus['score']); ?>">
                            <span><?php echo htmlspecialchars($passwordStatus['text']); ?></span>
                        </div>
                        <button class="btn btn-primary" onclick="goToModule('password')">
                            <?php echo isset($progress['password']) ? 'Review module' : 'Open module'; ?>
                        </button>
                    </div>

                    <div class="training-card <?php echo isset($progress['data']) ? 'completed' : ''; ?>">
                        <div class="feature-icon">🛡️</div>
                        <h3>Data Protection</h3>
                        <p>Keep company and customer data safe with simple day-to-day practices.</p>
                        <div class="module-meta <?php echo getScoreClass($dataStatus['score']); ?>">
                            <span><?php echo htmlspecialchars($dataStatus['text']); ?></span>
                        </div>
                        <button class="btn btn-primary" onclick="goToModule('data')">
                            <?php echo isset($progress['data']) ? 'Review module' : 'Open module'; ?>
                        </button>
                    </div>

                    <div class="training-card <?php echo isset($progress['browsing']) ? 'completed' : ''; ?>">
                        <div class="feature-icon">💻</div>
                        <h3>Safe Browsing</h3>
                        <p>Browse securely, detect malicious popups, and harden your devices.</p>
                        <div class="module-meta <?php echo getScoreClass($browsingStatus['score']); ?>">
                            <span><?php echo htmlspecialchars($browsingStatus['text']); ?></span>
                        </div>
                        <button class="btn btn-primary" onclick="goToModule('browsing')">
                            <?php echo isset($progress['browsing']) ? 'Review module' : 'Open module'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function goBackToDashboard() {
            window.location.href = 'employee.php#training';
        }

        function goToModule(key) {
            if (key === 'phishing') window.location.href = 'training_phishing.php';
            if (key === 'password') window.location.href = 'training_password.php';
            if (key === 'data') window.location.href = 'training_data.php';
            if (key === 'browsing') window.location.href = 'training_browsing.php';
        }
    </script>
</body>
</html>


