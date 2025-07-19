<?php
session_start();
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header('Location: admin_dashboard.php');
    exit();
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['is_admin'] = true;
        header('Location: admin_dashboard.php');
        exit();
    } else {
        $error = 'Invalid admin credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .admin-login-container { max-width: 400px; margin: 80px auto; background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(60,60,60,0.15); padding: 40px 32px; text-align: center; }
        .admin-login-container h1 { color: #f76b1c; margin-bottom: 24px; }
        .admin-login-container input { width: 90%; padding: 14px 10px; font-size: 1.1em; border: 1px solid #fda085; border-radius: 8px; margin-bottom: 16px; background: #f9f9f9; }
        .admin-login-container button { width: 100%; padding: 14px 0; font-size: 1.1em; border-radius: 8px; background: #ff6600; color: #fff; border: none; margin-top: 8px; cursor: pointer; }
        .admin-login-container .error-message { background: #ffe0e0; color: #c0392b; border-radius: 6px; padding: 8px 0; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <h1>Admin Login</h1>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html> 