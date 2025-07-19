<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin_login.php');
    exit();
}
require_once 'db.php';
// Handle topic addition
$topic_msg = '';
if (isset($_POST['add_topic'])) {
    $new_topic = trim($_POST['new_topic'] ?? '');
    if ($new_topic) {
        $stmt = $pdo->prepare('INSERT INTO topics (name) VALUES (?)');
        $stmt->execute([$new_topic]);
        $topic_msg = 'Topic added!';
    } else {
        $topic_msg = 'Please enter a topic name.';
    }
}
// Handle topic deletion
if (isset($_POST['delete_topic'])) {
    $tid = intval($_POST['topic_id']);
    $stmt = $pdo->prepare('DELETE FROM topics WHERE id = ?');
    $stmt->execute([$tid]);
}
// Fetch topics
$topics = $pdo->query('SELECT * FROM topics')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topic Management</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .admin-section { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(60,60,60,0.15); padding: 40px 36px; margin: 40px auto; max-width: 700px; }
        .admin-section h2 { color: #f76b1c; margin-bottom: 18px; font-size: 2em; text-align: left; }
        .admin-btn { background: #ff6600; color: #fff; border: none; border-radius: 8px; padding: 10px 28px; font-size: 1.13em; cursor: pointer; margin: 0 2px; transition: background 0.2s; }
        .admin-btn.delete { background: #c0392b; }
        .admin-btn:hover { background: #ff944d; }
        .admin-btn.delete:hover { background: #e74c3c; }
        .admin-msg { color: #27ae60; margin-bottom: 10px; font-weight: 500; }
        .topic-table-left { display: block; margin-bottom: 36px; min-width: 340px; width: 100%; }
        .topic-table-left .admin-table { margin: 0 auto; width: 100%; }
        .topic-table-left .admin-table th, .topic-table-left .admin-table td { padding: 10px 12px; height: 38px; }
        @media (max-width: 900px) {
            .admin-section { padding: 10px 2vw; }
        }
    </style>
</head>
<body>
    <div class="admin-section">
        <a href="admin_dashboard.php" class="admin-btn" style="margin-bottom:18px;">&larr; Back to Dashboard</a>
        <h2>Topic Management</h2>
        <?php if ($topic_msg): ?><div class="admin-msg"><?php echo htmlspecialchars($topic_msg); ?></div><?php endif; ?>
        <form method="post" style="margin-bottom:18px; display:flex; gap:10px; align-items:center;">
            <input type="text" name="new_topic" placeholder="Add new topic" required style="padding:8px 12px; border-radius:6px; border:1px solid #fda085; font-size:1.08em;">
            <button class="admin-btn" name="add_topic">Add Topic</button>
        </form>
        <div class="topic-table-left">
            <table class="admin-table">
                <tr><th>ID</th><th>Topic</th><th>Action</th></tr>
                <?php foreach ($topics as $t): ?>
                <tr>
                    <td><?php echo $t['id']; ?></td>
                    <td><?php echo htmlspecialchars($t['name']); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="topic_id" value="<?php echo $t['id']; ?>">
                            <button class="admin-btn delete" name="delete_topic">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html> 