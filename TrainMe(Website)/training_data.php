<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = "Data Protection Training - TrainMe";
$score = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = [
        'q1' => $_POST['q1'] ?? '',
        'q2' => $_POST['q2'] ?? '',
        'q3' => $_POST['q3'] ?? '',
    ];

    $correct = [
        'q1' => 'c',
        'q2' => 'b',
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
    $_SESSION['training_progress']['data'] = [
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
                VALUES (:user_id, 'data', :score, NOW())
                ON DUPLICATE KEY UPDATE 
                    score = VALUES(score),
                    completed_at = NOW(),
                    updated_at = NOW()
            ");
            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'score' => $score
            ]);
        } catch (PDOException $e) {
            error_log("Error saving training progress: " . $e->getMessage());
        }
    }

    $message = $score === 100
        ? "Great! You’re keeping data safe."
        : "Review the guidance below to strengthen your data protection habits.";
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
                    <h2>Data Protection</h2>
                    <p>Protect customer and company data in your daily work.</p>
                </div>
            </div>

            <div class="quiz-intro">
                <p>Key ideas:</p>
                <ul>
                    <li>Only store sensitive data where it is approved and necessary.</li>
                    <li>Lock your screen when you step away from your device.</li>
                    <li>Never share data externally without approval.</li>
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
                    <h3>1. You find a USB drive in the parking lot. What should you do?</h3>
                    <label><input type="radio" name="q1" value="a" required> Plug it into your work computer to see who it belongs to.</label>
                    <label><input type="radio" name="q1" value="b"> Take it home and check it there.</label>
                    <label><input type="radio" name="q1" value="c"> Give it to IT or security without plugging it in.</label>
                </div>

                <div class="quiz-question">
                    <h3>2. Which of these is the safest way to share a sensitive file internally?</h3>
                    <label><input type="radio" name="q2" value="a" required> Send it from your personal email account.</label>
                    <label><input type="radio" name="q2" value="b"> Use an approved, encrypted company tool.</label>
                    <label><input type="radio" name="q2" value="c"> Upload it to any free file-sharing site.</label>
                </div>

                <div class="quiz-question">
                    <h3>3. When is it okay to take customer data home on a personal device?</h3>
                    <label><input type="radio" name="q3" value="a" required> Never, unless explicitly approved and protected by company policy.</label>
                    <label><input type="radio" name="q3" value="b"> Whenever you are working late.</label>
                    <label><input type="radio" name="q3" value="c"> If the customer says it is fine.</label>
                </div>

                <button type="submit" class="btn btn-primary">Submit answers</button>
            </form>
        </div>
    </section>
</main>
</body>
</html>


