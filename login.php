<?php
session_start();
require_once 'db.php';

// If already logged in, redirect to index.php
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = trim($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username_email && $password) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username_email, $username_email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            if (isset($user['active']) && !$user['active']) {
                $error = 'Your account is deactivated. Please contact admin.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: index.php');
                exit();
            }
        } else {
            $error = 'Invalid username/email or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
    <div class="container">
        <h1>Login</h1>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="login.php" class="login-form">
            <div>
                <input type="text" name="username_email" placeholder="Username or Email" required>
            </div>
            <div>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="next-btn">Login</button>
        </form>
        <div class="auth-links">
            <a href="register.php">Sign Up</a> |
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </div>
</body>
</html> 