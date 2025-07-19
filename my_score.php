<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?msg=login_required');
    exit();
}
require_once 'db.php';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM scores WHERE user_id = ? ORDER BY taken_at DESC');
$stmt->execute([$user_id]);
$scores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Scores</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .score-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }
        .score-table th, .score-table td {
            border: 1px solid #fda085;
            padding: 10px 14px;
            text-align: center;
        }
        .score-table th {
            background: #fda08533;
            color: #f76b1c;
        }
        .score-table tr:nth-child(even) {
            background: #fff6ee;
        }
        .preview-eye {
            cursor: pointer;
            font-size: 1.3em;
            color: #f76b1c;
            transition: color 0.2s;
        }
        .preview-eye:hover {
            color: #ff6600;
        }
        .modal-bg {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.25);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-bg.active { display: flex; }
        .modal-content {
            background: #fff;
            border-radius: 14px;
            padding: 32px 28px;
            min-width: 340px;
            max-width: 90vw;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 8px 32px rgba(60,60,60,0.18);
        }
        .modal-close {
            float: right;
            font-size: 1.3em;
            color: #c0392b;
            cursor: pointer;
            margin-top: -12px;
            margin-right: -8px;
        }
    </style>
</head>
<body>
    <div class="auth-nav">
        <span class="username">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="logout.php">Logout</a>
        <button class="my-score-btn" onclick="window.location.href='my_score.php'">My Score</button>
    </div>
    <div class="container">
        <div style="margin-bottom: 18px; text-align:left;">
            <a href="index.php" class="next-btn" style="text-decoration:none; display:inline-block;">Back to Home</a>
        </div>
        <h1 style="clear:left;">My Quiz Scores</h1>
        <?php if (count($scores) === 0): ?>
            <p>You have not taken any quizzes yet.</p>
        <?php else: ?>
            <table class="score-table">
                <tr>
                    <th>Date</th>
                    <th>Topic</th>
                    <th>Difficulty</th>
                    <th>Score</th>
                    <th>Right</th>
                    <th>Wrong</th>
                    <th>Preview</th>
                </tr>
                <?php foreach ($scores as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($row['taken_at']))); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($row['topic'])); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($row['difficulty'])); ?></td>
                    <td><?php echo htmlspecialchars($row['score']); ?> / 5</td>
                    <td><?php echo htmlspecialchars($row['right_answers']); ?></td>
                    <td><?php echo htmlspecialchars($row['wrong_answers']); ?></td>
                    <td><span class="preview-eye" onclick="showPreview(<?php echo $row['id']; ?>)">&#128065;</span></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
    <div class="modal-bg" id="previewModal">
        <div class="modal-content" id="modalContent">
            <span class="modal-close" onclick="closePreview()">&times;</span>
            <div id="modalBody">Loading...</div>
        </div>
    </div>
    <script>
    function showPreview(scoreId) {
        fetch('score_preview.php?id=' + scoreId)
            .then(res => res.text())
            .then(html => {
                document.getElementById('modalBody').innerHTML = html;
                document.getElementById('previewModal').classList.add('active');
            });
    }
    function closePreview() {
        document.getElementById('previewModal').classList.remove('active');
    }
    </script>
</body>
</html> 