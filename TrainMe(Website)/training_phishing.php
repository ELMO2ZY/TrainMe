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

$page_title = "Phishing Awareness Training - TrainMe";
$user_name = $_SESSION['user_name'] ?? 'Employee';
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
    $_SESSION['training_progress']['phishing'] = [
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
                VALUES (:user_id, 'phishing', :score, NOW())
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
                // Get user email from database
                $emailStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                $emailStmt->execute([$_SESSION['user_id']]);
                $user = $emailStmt->fetch();
                
                if ($user && !empty($user['email'])) {
                    $emailHelper = new TrainMeEmail();
                    $emailHelper->sendTestResultsEmail(
                        $user['email'],
                        $_SESSION['user_name'] ?? 'Employee',
                        'Phishing Awareness',
                        $score,
                        $total
                    );
                }
            } catch (Exception $e) {
                // Log error but don't fail the training completion
                error_log("Test results email error: " . $e->getMessage());
            } catch (Throwable $e) {
                // Catch any other errors (warnings, etc.)
                error_log("Test results email error: " . $e->getMessage());
            }
        } catch (PDOException $e) {
            error_log("Error saving training progress: " . $e->getMessage());
        }
    }

    $message = $score === 100
        ? "Excellent work! You answered all questions correctly."
        : "Nice effort! Review the explanations below and try again to improve your score.";
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
                    <h2>Phishing Awareness</h2>
                    <p>Learn to recognize and avoid common phishing techniques.</p>
                </div>
            </div>

            <div class="quiz-intro">
                <p>Read the brief tips, then answer the questions to check your understanding.</p>
                <ul>
                    <li>Always hover over links to inspect the real destination.</li>
                    <li>Be suspicious of urgent language and requests for credentials.</li>
                    <li>Verify unexpected emails via a second channel (phone, chat, official portal).</li>
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
                    <h3>1. You receive an email from “IT Support” asking you to reset your password using a link. What do you do?</h3>
                    <label><input type="radio" name="q1" value="a" required> Click the link immediately and reset your password.</label>
                    <label><input type="radio" name="q1" value="b"> Open a new browser window and go directly to the official company portal.</label>
                    <label><input type="radio" name="q1" value="c"> Reply to the email with your current password to confirm.</label>
                </div>

                <div class="quiz-question">
                    <h3>2. Which of the following is the strongest clue that an email is a phishing attempt?</h3>
                    <label><input type="radio" name="q2" value="a" required> It includes your first name.</label>
                    <label><input type="radio" name="q2" value="b"> It comes from a large, well-known company.</label>
                    <label><input type="radio" name="q2" value="c"> The sender’s address is slightly misspelled (e.g., support@micr0soft.com).</label>
                </div>

                <div class="quiz-question">
                    <h3>3. You clicked a suspicious link by mistake. What is the best immediate action?</h3>
                    <label><input type="radio" name="q3" value="a" required> Disconnect from the network and report it to security right away.</label>
                    <label><input type="radio" name="q3" value="b"> Ignore it if nothing obvious happened.</label>
                    <label><input type="radio" name="q3" value="c"> Forward the email to your colleagues as a warning.</label>
                </div>

                <button type="submit" class="btn btn-primary">Submit answers</button>
            </form>
        </div>
    </section>
</main>
</body>
</html>


