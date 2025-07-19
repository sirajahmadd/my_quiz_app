<?php
session_start();
require_once 'db.php';

// PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        // Check if email exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            // Generate token and expiry
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now

            // Save to DB
            $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?');
            $stmt->execute([$token, $expiry, $user['id']]);

            // Send email
            $mail = new PHPMailer(true);
            try {
                // SMTP config
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'sirajahmd186@gmail.com'; // <-- your Gmail
                $mail->Password = '!Google@3210'; // <-- your Gmail App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('YOUR_GMAIL_ADDRESS@gmail.com', 'Quiz App');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $reset_link = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reset_password.php?token=' . $token;
                $mail->Body = \"<p>Click the link below to reset your password:</p>
                                <p><a href='$reset_link'>$reset_link</a></p>
                                <p>This link will expire in 1 hour.</p>\";

                $mail->send();
            } catch (Exception $e) {
                // For debugging: $error = 'Mailer Error: ' . $mail->ErrorInfo;
            }
        }
        $success = 'If this email is registered, a password reset link has been sent.';
    } else {
        $error = 'Please enter your email.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="auth-nav">
        <a href="login.php">Login</a>
        <a href="register.php">Sign Up</a>
    </div>
    <div class="container">
        <h1>Forgot Password</h1>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post" action="forgot_password.php" class="login-form">
            <div>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="next-btn">Send Reset Link</button>
        </form>
        <div class="auth-links">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</body>
</html> 