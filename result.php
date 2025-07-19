<?php session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?msg=login_required');
    exit();
}
$score = $_SESSION['score'] ?? 0;
$right = $_SESSION['right'] ?? 0;
$wrong = $_SESSION['wrong'] ?? 0;
$questions = $_SESSION['questions'] ?? [];
$user_answers = $_SESSION['user_answers'] ?? [];

// Save score to database before destroying session
if (isset($_SESSION['user_id'])) {
    require_once 'db.php';
    $user_id = $_SESSION['user_id'];
    $topic = $_SESSION['topic'] ?? '';
    $difficulty = $_SESSION['difficulty'] ?? '';
    // Prepare quiz details for preview (question, options, correct, user)
    $quiz_details = [];
    foreach ($questions as $i => $q) {
        $quiz_details[] = [
            'question' => $q['question'],
            'options' => $q['options'],
            'correct' => $q['answer'],
            'user' => $user_answers[$i] ?? null
        ];
    }
    $details_json = json_encode($quiz_details);
    $stmt = $pdo->prepare('INSERT INTO scores (user_id, score, right_answers, wrong_answers, topic, difficulty, details) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$user_id, $score, $right, $wrong, $topic, $difficulty, $details_json]);
}

// Unset only quiz-related session variables, keep user logged in
unset($_SESSION['score'], $_SESSION['right'], $_SESSION['wrong'], $_SESSION['questions'], $_SESSION['topic'], $_SESSION['difficulty'], $_SESSION['current'], $_SESSION['user_answers']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="auth-nav">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="username">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php">Logout</a>
            <button class="my-score-btn" onclick="window.location.href='my_score.php'">My Score</button>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Sign Up</a>
        <?php endif; ?>
    </div>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'login_required'): ?>
        <div class="error-message">You must be logged in to access the quiz.</div>
    <?php endif; ?>
    <div class="container">
        <h1>Congratulations!</h1>
        <p>You have scored: <strong><?php echo $score; ?> / 5</strong></p>
        <p>Right Answers: <?php echo $right; ?> | Wrong Answers: <?php echo $wrong; ?></p>
        <div class="result-buttons">
            <a href="index.php"><button>Play Again</button></a>
            <a href="index.php"><button>Quit</button></a>
        </div>
        <?php if (!empty($questions) && !empty($user_answers)): ?>
        <h2 style="margin-top:32px;">Quiz Review</h2>
        <div style="margin-top:18px; text-align:left;">
            <?php foreach ($questions as $i => $q): ?>
                <div style="margin-bottom: 28px; padding: 18px 18px 10px 18px; background: #fff8f2; border-radius: 10px; box-shadow: 0 2px 8px #fda08522;">
                    <div style="font-weight:600; color:#f76b1c; margin-bottom:8px;">Q<?php echo ($i+1); ?>. <?php echo htmlspecialchars($q['question']); ?></div>
                    <div style="margin-left: 12px;">
                        <?php foreach ($q['options'] as $key => $option):
                            $is_correct = ($key === $q['answer']);
                            $is_user = (isset($user_answers[$i]) && $user_answers[$i] === $key);
                            $option_style = '';
                            if ($is_correct) {
                                $option_style = 'background:#d4f8e5;color:#218838;font-weight:600;';
                            }
                            if ($is_user && !$is_correct) {
                                $option_style = 'background:#ffe0e0;color:#c0392b;font-weight:600;';
                            }
                            if ($is_user && $is_correct) {
                                $option_style = 'background:#d4f8e5;color:#218838;font-weight:600; border:2px solid #27ae60;';
                            }
                        ?>
                        <div style="display:inline-block; min-width: 60px; margin: 4px 0; padding: 6px 16px; border-radius: 6px; <?php echo $option_style; ?>">
                            <?php echo htmlspecialchars($option); ?>
                            <?php if ($is_user): ?>
                                <span style="font-size:0.95em; opacity:0.7;">(Your answer)</span>
                            <?php endif; ?>
                            <?php if ($is_correct): ?>
                                <span style="font-size:0.95em; opacity:0.7;">(Correct)</span>
                            <?php endif; ?>
                        </div><br>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 