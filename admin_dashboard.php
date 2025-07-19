<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .admin-section { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(60,60,60,0.15); padding: 40px 36px; margin: 40px auto; max-width: 600px; text-align: center; }
        .admin-section h2 { color: #f76b1c; margin-bottom: 32px; font-size: 2.2em; }
        .dashboard-btns { display: flex; flex-direction: column; gap: 28px; align-items: center; }
        .dashboard-btn {
            background: linear-gradient(90deg, #fda085 0%, #f76b1c 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 28px 60px;
            font-size: 1.5em;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 16px rgba(60,60,60,0.07);
            transition: background 0.2s, transform 0.2s;
            text-decoration: none;
            display: block;
            width: 100%;
            max-width: 400px;
        }
        .dashboard-btn:hover {
            background: linear-gradient(90deg, #f76b1c 0%, #fda085 100%);
            transform: translateY(-2px) scale(1.03);
        }
    </style>
</head>
<body>
    <div class="admin-section">
        <h2>Admin Dashboard</h2>
        <div class="dashboard-btns">
            <a href="admin_topics.php" class="dashboard-btn">Topic Management</a>
            <a href="admin_users.php" class="dashboard-btn">User Management</a>
            <a href="admin_questions.php" class="dashboard-btn">Question Management</a>
        </div>
    </div>
</body>
</html> 