<?php session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?msg=login_required');
    exit();
}
require_once 'db.php';
$topics = $pdo->query('SELECT * FROM topics')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Topic</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #ffe0c3 0%, #ffb973 100%);
            margin: 0;
            font-family: 'Segoe UI', 'Arial', sans-serif;
        }
        .container {
            max-width: 480px;
            margin: 60px auto 0 auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(60,60,60,0.13);
            padding: 44px 36px 36px 36px;
            text-align: center;
        }
        h1 {
            color: #f76b1c;
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 32px;
            letter-spacing: 1px;
        }
        .topic-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px 32px;
            justify-content: center;
            margin-top: 18px;
        }
        .topic-buttons button {
            background: linear-gradient(90deg, #fda085 0%, #f76b1c 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            width: 180px;
            height: 60px;
            font-size: 1.18em;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 12px rgba(60,60,60,0.07);
            transition: background 0.2s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .topic-buttons button:hover {
            background: linear-gradient(90deg, #f76b1c 0%, #fda085 100%);
            transform: translateY(-2px) scale(1.04);
        }
        @media (max-width: 600px) {
            .container { padding: 18px 4vw; }
            h1 { font-size: 1.3em; }
            .topic-buttons {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .topic-buttons button { width: 100%; height: 48px; font-size: 1em; }
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
    <div class="container">
        <h1>Choose your topic</h1>
        <?php
        $difficulty = isset($_GET['difficulty']) ? htmlspecialchars($_GET['difficulty']) : 'easy';
        ?>
        <form action="quiz.php" method="get">
            <input type="hidden" name="difficulty" value="<?php echo $difficulty; ?>">
            <div class="topic-buttons">
                <?php foreach ($topics as $topic): ?>
                    <button type="submit" name="topic" value="<?php echo htmlspecialchars($topic['name']); ?>"><?php echo htmlspecialchars(ucfirst($topic['name'])); ?></button>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</body>
</html> 