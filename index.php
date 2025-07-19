<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Quiz</title>
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
    <div class="container home-container">
        <h1 class="home-title">Welcome to Quiz</h1>
        <p class="home-subtitle">Check your knowledge</p>
        <h2 class="home-difficulty">Choose your difficulty level</h2>
        <form action="topic.php" method="get">
            <div class="difficulty-buttons">
                <button type="submit" name="difficulty" value="easy">Easy</button>
                <button type="submit" name="difficulty" value="medium">Medium</button>
                <button type="submit" name="difficulty" value="hard">Hard</button>
            </div>
        </form>
    </div>
    <style>
    .home-container {
        max-width: 600px;
        min-width: 400px;
        margin: 70px auto 0 auto;
        padding: 60px 40px 50px 40px;
        border-radius: 24px;
        box-shadow: 0 8px 40px rgba(60,60,60,0.18);
        background: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .home-title {
        font-size: 2.8em;
        color: #f76b1c;
        font-weight: 800;
        margin-bottom: 18px;
        letter-spacing: 1px;
        text-align: center;
    }
    .home-subtitle {
        font-size: 1.5em;
        color: #ff6600;
        margin-bottom: 32px;
        font-weight: 500;
        text-align: center;
    }
    .home-difficulty {
        font-size: 1.35em;
        color: #333;
        margin-bottom: 28px;
        font-weight: 700;
        text-align: center;
    }
    .difficulty-buttons {
        gap: 24px;
        margin-bottom: 0;
    }
    .difficulty-buttons button {
        font-size: 1.35em;
        padding: 20px 48px;
        border-radius: 12px;
        margin: 0 18px;
        font-weight: 700;
        box-shadow: 0 2px 12px rgba(247,107,28,0.10);
    }
    @media (max-width: 700px) {
        .home-container { max-width: 98vw; min-width: unset; padding: 18px 4vw 18px 4vw; }
        .home-title { font-size: 2em; }
        .home-subtitle { font-size: 1.1em; }
        .home-difficulty { font-size: 1em; }
        .difficulty-buttons button { font-size: 1em; padding: 14px 0; margin: 0 6px; }
    }
    </style>
</body>
</html> 