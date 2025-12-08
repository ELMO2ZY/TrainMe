<?php
// Suppress errors from being displayed (they'll still be logged)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Include email helper (suppress errors)
try {
    require_once __DIR__ . '/email_helper.php';
} catch (Throwable $e) {
    error_log("Email helper include error: " . $e->getMessage());
}

$page_title = "Safe Browsing Training - TrainMe";
$score = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = [
        'q1' => $_POST['q1'] ?? '',
        'q2' => $_POST['q2'] ?? '',
        'q3' => $_POST['q3'] ?? '',
    ];

    $correct = [
        'q1' => 'b',
        'q2' => 'c',
        'q3' => 'a',
    ];

    $total = count($correct);
    $correctCount = 0;
    foreach ($correct as $key => $value) {
        if ($answers[$key] === $value) {
            $correctCount++;
        }
    }

    $score = (int) round(($correctCount / $total) * 100);
    $_SESSION['training_progress']['browsing'] = [
        'score' => $score,
        'completed_at' => date('Y-m-d H:i:s'),
    ];
    
    // Save to database
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
                INSERT INTO training_progress (user_id, module_key, score, completed_at)
                VALUES (:user_id, 'browsing', :score, NOW())
                ON DUPLICATE KEY UPDATE 
                    score = VALUES(score),
                    completed_at = NOW(),
                    updated_at = NOW()
            ");
            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'score' => $score
            ]);
            
            // Send test results email
            try {
                $emailStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                $emailStmt->execute([$_SESSION['user_id']]);
                $user = $emailStmt->fetch();
                
                if ($user && !empty($user['email'])) {
                    $emailHelper = new TrainMeEmail();
                    $emailHelper->sendTestResultsEmail(
                        $user['email'],
                        $_SESSION['user_name'] ?? 'Employee',
                        'Safe Browsing',
                        $score,
                        $total
                    );
                }
            } catch (Exception $e) {
                error_log("Test results email error: " . $e->getMessage());
            } catch (Throwable $e) {
                error_log("Test results email error: " . $e->getMessage());
            }
        } catch (PDOException $e) {
            error_log("Error saving training progress: " . $e->getMessage());
        }
    }

    $message = $score === 100
        ? "Excellent! Your browsing habits are secure."
        : "Review the safe browsing practices and try again.";
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
<main class="main-content">
    <section class="training">
        <div class="training-container">
            <div class="training-page-top">
                <button class="btn btn-outline back-button" onclick="window.location.href='training.php'">← Back to modules</button>
                <div class="training-page-heading">
                    <h2>Safe Browsing</h2>
                    <p>Stay safe online when using websites, downloads, and Wi‑Fi.</p>
                </div>
            </div>

            <div class="quiz-intro">
                <p>Safe browsing basics:</p>
                <ul>
                    <li>Install updates and security patches promptly.</li>
                    <li>Avoid downloading software from untrusted sources.</li>
                    <li>Be cautious when using public Wi‑Fi; prefer VPN when available.</li>
                </ul>
            </div>

            <?php if ($score !== null): ?>
                <div class="quiz-result">
                    <p><strong>Your score:</strong> <?php echo $score; ?>%</p>
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" class="quiz-form">
                <div class="quiz-question">
                    <h3>1. Which website URL is safest to log into your bank?</h3>
                    <label><input type="radio" name="q1" value="a" required> http://mybank-login.com</label>
                    <label><input type="radio" name="q1" value="b"> https://bankname.com</label>
                    <label><input type="radio" name="q1" value="c"> http://bankname.secure-login.net</label>
                </div>

                <div class="quiz-question">
                    <h3>2. You see a popup saying your computer is infected and to call a phone number. What should you do?</h3>
                    <label><input type="radio" name="q2" value="a" required> Call the number immediately.</label>
                    <label><input type="radio" name="q2" value="b"> Click the popup to download the “fix”.</label>
                    <label><input type="radio" name="q2" value="c"> Close the browser, run your approved antivirus, and report it if needed.</label>
                </div>

                <div class="quiz-question">
                    <h3>3. When using public Wi‑Fi, which behavior is safest?</h3>
                    <label><input type="radio" name="q3" value="a" required> Avoid accessing sensitive services unless using a VPN.</label>
                    <label><input type="radio" name="q3" value="b"> Log in to all your accounts to check that they work.</label>
                    <label><input type="radio" name="q3" value="c"> Turn off your device’s firewall to improve the connection.</label>
                </div>

                <button type="submit" class="btn btn-primary">Submit answers</button>
            </form>
        </div>
    </section>
</main>
</body>
</html>


