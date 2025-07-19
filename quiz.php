<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?msg=login_required');
    exit();
}
require_once 'db.php';

// Get topic and difficulty from GET or session
if (isset($_GET['topic']) && isset($_GET['difficulty'])) {
    $_SESSION['topic'] = $_GET['topic'];
    $_SESSION['difficulty'] = $_GET['difficulty'];
    $_SESSION['score'] = 0;
    $_SESSION['right'] = 0;
    $_SESSION['wrong'] = 0;
    $_SESSION['current'] = 0;
    // Fetch questions from DB
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE topic = ? AND difficulty = ?');
    $stmt->execute([$_SESSION['topic'], $_SESSION['difficulty']]);
    $filtered = $stmt->fetchAll();
    if (count($filtered) < 5) {
        echo '<script>alert("Not enough questions for this topic and difficulty. Please choose another."); window.location.href = "topic.php?difficulty=' . $_SESSION['difficulty'] . '";</script>';
        exit();
    }
    shuffle($filtered);
    // Convert DB rows to the same format as before
    $questions = [];
    foreach (array_slice($filtered, 0, 5) as $row) {
        $questions[] = [
            'question' => $row['question'],
            'options' => [
                'a' => $row['option_a'],
                'b' => $row['option_b'],
                'c' => $row['option_c'],
                'd' => $row['option_d']
            ],
            'answer' => $row['answer'],
            'topic' => $row['topic'],
            'difficulty' => $row['difficulty']
        ];
    }
    $_SESSION['questions'] = $questions;
}

// Handle answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['option'] ?? '';
    $current = $_SESSION['current'];
    $correct = $_SESSION['questions'][$current]['answer'];
    // Store user's answer for review
    if (!isset($_SESSION['user_answers'])) {
        $_SESSION['user_answers'] = [];
    }
    $_SESSION['user_answers'][$current] = $selected;
    if ($selected === $correct) {
        $_SESSION['score'] += 1;
        $_SESSION['right'] += 1;
    } else {
        $_SESSION['wrong'] += 1;
    }
    $_SESSION['current']++;
    // If finished, redirect to result
    if ($_SESSION['current'] >= 5) {
        header('Location: result.php');
        exit();
    } else {
        // Redirect to avoid form resubmission
        header('Location: quiz.php');
        exit();
    }
}
// SAFETY CHECK: If current is out of bounds, redirect to result
if (!isset($_SESSION['questions']) || !isset($_SESSION['current']) || $_SESSION['current'] >= 5) {
    header('Location: result.php');
    exit();
}
$current = $_SESSION['current'] ?? 0;
$question = $_SESSION['questions'][$current];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #ffe0c3 0%, #ffb973 100%);
            margin: 0;
            font-family: 'Segoe UI', 'Arial', sans-serif;
        }
        .quiz-container {
            max-width: 540px;
            margin: 60px auto 0 auto;
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(60,60,60,0.13);
            padding: 44px 36px 36px 36px;
            text-align: center;
        }
        .quiz-container h2 {
            color: #f76b1c;
            font-size: 1.7em;
            font-weight: 700;
            margin-bottom: 28px;
            letter-spacing: 1px;
        }
        .quiz-form {
            margin-top: 0;
        }
        .question-text {
            font-size: 1.25em;
            font-weight: 700;
            color: #222;
            margin-bottom: 32px;
            margin-top: 0;
            line-height: 1.35;
        }
        .options-group {
            display: flex;
            flex-direction: column;
            gap: 18px;
            align-items: flex-start;
            margin: 0 auto 36px auto;
            max-width: 90%;
        }
        .option-item {
            display: flex;
            align-items: center;
            font-size: 1.13em;
            font-weight: 500;
            color: #222;
        }
        .option-item input[type="radio"] {
            accent-color: #f76b1c;
            width: 20px;
            height: 20px;
            margin-right: 12px;
        }
        .option-item label {
            cursor: pointer;
            padding: 4px 0;
        }
        .next-btn {
            background: linear-gradient(90deg, #fda085 0%, #f76b1c 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 16px 0;
            font-size: 1.18em;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 18px;
            transition: background 0.2s, transform 0.2s;
        }
        .next-btn:hover {
            background: linear-gradient(90deg, #f76b1c 0%, #fda085 100%);
            transform: translateY(-2px) scale(1.03);
        }
        @media (max-width: 600px) {
            .quiz-container { padding: 18px 4vw; }
            .quiz-container h2 { font-size: 1.1em; }
            .question-text { font-size: 1em; }
            .option-item { font-size: 1em; }
            .next-btn { font-size: 1em; padding: 12px 0; }
        }
    </style>
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
    <div class="container quiz-container">
        <h2>Question <?php echo $current + 1; ?> of 5</h2>
        <form method="post" class="quiz-form">
            <p class="question-text"><?php echo htmlspecialchars(
                $question['question']); ?></p>
            <div class="options-group">
            <?php foreach ($question['options'] as $key => $option): ?>
                <div class="option-item">
                    <input type="radio" id="opt_<?php echo $key; ?>" name="option" value="<?php echo $key; ?>" required>
                    <label for="opt_<?php echo $key; ?>"><?php echo htmlspecialchars($option); ?></label>
                </div>
            <?php endforeach; ?>
            </div>
            <button type="submit" class="next-btn">Next</button>
        </form>
    </div>
</body>
</html> 
