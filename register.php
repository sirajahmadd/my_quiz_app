<?php
session_start();
require_once 'db.php';

// If already logged in, redirect to index.php
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if ($username && $email && $password && $confirm_password) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            // Check if username or email already exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'Username or email already exists.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
                if ($stmt->execute([$username, $email, $hashed])) {
                    $success = 'Registration successful! You can now <a href="login.php">login</a>.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
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
    <title>Register</title>
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
        <h1>Sign Up</h1>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post" action="register.php" class="login-form">
            <div>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <button type="submit" class="next-btn">Register</button>
        </form>
        <div class="auth-links">
            <a href="login.php">Already have an account? Login</a>
        </div>
    </div>
</body>
</html> 