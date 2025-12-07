<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = "Phishing Checklist - TrainMe";
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
                <button class="btn btn-outline back-button" onclick="window.location.href='employee.php#resources'">← Back to resources</button>
                <div class="training-page-heading">
                    <h2>Phishing quick checklist</h2>
                    <p>Run through these checks before you click, reply, or download.</p>
                </div>
            </div>

            <div class="quiz-intro">
                <ul>
                    <li><strong>Check the sender:</strong> Is the address spelled correctly and expected?</li>
                    <li><strong>Hover over links:</strong> Does the real URL match the text and brand?</li>
                    <li><strong>Watch the tone:</strong> Is there unusual urgency, threats, or pressure?</li>
                    <li><strong>Look for mistakes:</strong> Poor grammar, generic greetings, or odd logos.</li>
                    <li><strong>Never share passwords:</strong> Legitimate services will not ask for them by email.</li>
                    <li><strong>Verify out-of-band:</strong> Use phone or chat you already trust, not the message itself.</li>
                    <li><strong>When in doubt:</strong> Don’t click—report it to the security team.</li>
                </ul>
            </div>
        </div>
    </section>
</main>
</body>
</html>


