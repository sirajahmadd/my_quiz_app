<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin_login.php');
    exit();
}
require_once 'db.php';
// Handle user activation/deactivation
if (isset($_POST['toggle_user'])) {
    $uid = intval($_POST['user_id']);
    $active = intval($_POST['active']);
    $stmt = $pdo->prepare('UPDATE users SET active = ? WHERE id = ?');
    $stmt->execute([$active, $uid]);
}
// Fetch users
$users = $pdo->query('SELECT id, username, email, active FROM users')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .admin-section { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(60,60,60,0.15); padding: 40px 36px; margin: 40px auto; max-width: 900px; }
        .admin-section h2 { color: #f76b1c; margin-bottom: 18px; font-size: 2em; text-align: left; }
        .admin-table {
            width: 98%;
            margin: 0 auto 32px auto;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 1.13em;
            background: #fff;
            box-shadow: 0 2px 16px rgba(60,60,60,0.07);
            border-radius: 14px;
            overflow: hidden;
        }
        .admin-table th, .admin-table td {
            border: 1px solid #fda085;
            padding: 18px 14px;
            text-align: center;
            vertical-align: middle;
        }
        .admin-table th {
            background: #fda08533;
            color: #f76b1c;
            font-size: 1.18em;
            font-weight: 700;
        }
        .admin-table tr:nth-child(even) { background: #fff6ee; }
        .admin-table tr:hover { background: #fff2e0; transition: background 0.2s; }
        .admin-btn { background: #ff6600; color: #fff; border: none; border-radius: 8px; padding: 10px 28px; font-size: 1.13em; cursor: pointer; margin: 0 2px; transition: background 0.2s; }
        .admin-btn.deactivate { background: #c0392b; }
        .admin-btn:hover { background: #ff944d; }
        .admin-btn.deactivate:hover { background: #e74c3c; }
        @media (max-width: 900px) {
            .admin-section { padding: 10px 2vw; }
            .admin-table th, .admin-table td { padding: 10px 4px; }
        }
    </style>
</head>
<body>
    <div class="admin-section">
        <a href="admin_dashboard.php" class="admin-btn" style="margin-bottom:18px;">&larr; Back to Dashboard</a>
        <h2>User Management</h2>
        <table class="admin-table">
            <tr><th>ID</th><th>Username</th><th>Email</th><th>Status</th><th>Action</th></tr>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo $u['active'] ? 'Active' : 'Inactive'; ?></td>
                <td>
                    <?php if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $u['id']): ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <input type="hidden" name="active" value="<?php echo $u['active'] ? 0 : 1; ?>">
                        <button class="admin-btn <?php echo $u['active'] ? 'deactivate' : ''; ?>" name="toggle_user"><?php echo $u['active'] ? 'Deactivate' : 'Activate'; ?></button>
                    </form>
                    <?php else: ?>
                    <span style="color:#888; font-size:0.98em;">(You)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html> 