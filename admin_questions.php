<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin_login.php');
    exit();
}
require_once 'db.php';
// Handle question deletion
if (isset($_POST['delete_question'])) {
    $qid = intval($_POST['question_id']);
    $stmt = $pdo->prepare('DELETE FROM questions WHERE id = ?');
    $stmt->execute([$qid]);
}
// Handle question addition
$add_msg = '';
if (isset($_POST['add_question'])) {
    $question = trim($_POST['question'] ?? '');
    $option_a = trim($_POST['option_a'] ?? '');
    $option_b = trim($_POST['option_b'] ?? '');
    $option_c = trim($_POST['option_c'] ?? '');
    $option_d = trim($_POST['option_d'] ?? '');
    $answer = $_POST['answer'] ?? '';
    $topic = trim($_POST['topic'] ?? '');
    $difficulty = trim($_POST['difficulty'] ?? '');
    if ($question && $option_a && $option_b && $option_c && $option_d && $answer && $topic && $difficulty) {
        $stmt = $pdo->prepare('INSERT INTO questions (question, option_a, option_b, option_c, option_d, answer, topic, difficulty) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$question, $option_a, $option_b, $option_c, $option_d, $answer, $topic, $difficulty]);
        $add_msg = 'Question added!';
    } else {
        $add_msg = 'Please fill all fields.';
    }
}
// Handle import questions from API
$import_msg = '';
if (isset($_POST['import_api'])) {
    $import_topic = trim($_POST['import_topic'] ?? '');
    $import_difficulty = trim($_POST['import_difficulty'] ?? '');
    $import_amount = intval($_POST['import_amount'] ?? 5);
    if ($import_topic && $import_difficulty && $import_amount > 0) {
        $api_url = "https://opentdb.com/api.php?amount={$import_amount}&difficulty={$import_difficulty}&type=multiple";
        $api_response = @file_get_contents($api_url);
        if ($api_response !== false) {
            $api_data = json_decode($api_response, true);
            if (isset($api_data['results']) && is_array($api_data['results'])) {
                $imported = 0;
                $skipped = 0;
                foreach ($api_data['results'] as $item) {
                    $question = html_entity_decode($item['question']);
                    $options = array_merge($item['incorrect_answers'], [$item['correct_answer']]);
                    shuffle($options);
                    $option_a = html_entity_decode($options[0]);
                    $option_b = html_entity_decode($options[1]);
                    $option_c = html_entity_decode($options[2]);
                    $option_d = html_entity_decode($options[3]);
                    $answer_idx = array_search(html_entity_decode($item['correct_answer']), $options);
                    $answer_letter = ['a', 'b', 'c', 'd'][$answer_idx];
                    $difficulty = $item['difficulty'];
                    $check_stmt = $pdo->prepare('SELECT COUNT(*) FROM questions WHERE question = ? AND topic = ?');
                    $check_stmt->execute([$question, $import_topic]);
                    $exists = $check_stmt->fetchColumn();
                    if (!$exists) {
                        $stmt = $pdo->prepare('INSERT INTO questions (question, option_a, option_b, option_c, option_d, answer, topic, difficulty) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                        $stmt->execute([$question, $option_a, $option_b, $option_c, $option_d, $answer_letter, $import_topic, $difficulty]);
                        $imported++;
                    } else {
                        $skipped++;
                    }
                }
                $import_msg = "Imported $imported questions from API! Skipped $skipped duplicate(s).";
            } else {
                $import_msg = 'API returned no questions.';
            }
        } else {
            $import_msg = 'Failed to fetch from API.';
        }
    } else {
        $import_msg = 'Please select topic, difficulty, and amount.';
    }
}
// Pagination for questions
$questions_per_page = 10;
$page = isset($_GET['qpage']) ? max(1, intval($_GET['qpage'])) : 1;
$offset = ($page - 1) * $questions_per_page;
$filter_topic = isset($_GET['filter_topic']) ? $_GET['filter_topic'] : '';
$filter_difficulty = isset($_GET['filter_difficulty']) ? $_GET['filter_difficulty'] : '';
$filter_sql = '';
$filter_params = [];
if ($filter_topic) {
    $filter_sql .= ' AND topic = ?';
    $filter_params[] = $filter_topic;
}
if ($filter_difficulty) {
    $filter_sql .= ' AND difficulty = ?';
    $filter_params[] = $filter_difficulty;
}
$total_questions = $pdo->prepare('SELECT COUNT(*) FROM questions WHERE 1=1' . $filter_sql);
$total_questions->execute($filter_params);
$total_questions = $total_questions->fetchColumn();
$total_pages = ceil($total_questions / $questions_per_page);
$q_sql = 'SELECT * FROM questions WHERE 1=1' . $filter_sql . ' LIMIT ' . $questions_per_page . ' OFFSET ' . $offset;
$q_stmt = $pdo->prepare($q_sql);
$q_stmt->execute($filter_params);
$questions = $q_stmt->fetchAll();
$topics = $pdo->query('SELECT * FROM topics')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Management</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .admin-section { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(60,60,60,0.15); padding: 40px 36px; margin: 40px auto; max-width: 1200px; }
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
        .admin-btn.delete { background: #c0392b; }
        .admin-btn:hover { background: #ff944d; }
        .admin-btn.deactivate:hover, .admin-btn.delete:hover { background: #e74c3c; }
        .admin-form {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr 1fr 1fr 1.2fr 1.2fr 1.2fr;
            gap: 12px 10px;
            align-items: center;
            margin-bottom: 18px;
        }
        .admin-form label { font-weight: 500; margin-right: 2px; text-align: right; }
        .admin-form input, .admin-form select {
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #fda085;
            font-size: 1.08em;
            width: 100%;
            box-sizing: border-box;
        }
        .admin-form button { grid-column: span 2; margin-top: 0; }
        .admin-msg { color: #27ae60; margin-bottom: 10px; font-weight: 500; }
        .filter-bar { display: flex; gap: 18px; align-items: center; margin-bottom: 18px; }
        .filter-bar select { padding: 8px 12px; border-radius: 6px; border: 1px solid #fda085; font-size: 1.08em; }
        @media (max-width: 1300px) {
            .admin-section { padding: 18px 4vw; max-width: 99vw; }
            .admin-table { font-size: 1em; }
            .admin-form { grid-template-columns: 1fr 1fr 1fr 1fr 1fr; }
        }
        @media (max-width: 900px) {
            .admin-section { padding: 10px 2vw; }
            .admin-form { grid-template-columns: 1fr 1fr; }
            .admin-table th, .admin-table td { padding: 10px 4px; }
        }
        .preview-eye { cursor: pointer; font-size: 1.3em; color: #f76b1c; transition: color 0.2s; }
        .preview-eye:hover { color: #ff6600; }
        .modal-bg { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.25); z-index: 1000; justify-content: center; align-items: center; }
        .modal-bg.active { display: flex; }
        .modal-content { background: #fff; border-radius: 14px; padding: 32px 28px; min-width: 340px; max-width: 90vw; max-height: 80vh; overflow-y: auto; box-shadow: 0 8px 32px rgba(60,60,60,0.18); }
        .modal-close { float: right; font-size: 1.3em; color: #c0392b; cursor: pointer; margin-top: -12px; margin-right: -8px; }
        .active-page { background:none!important; color:#f76b1c!important; pointer-events:none; font-weight:bold; text-decoration:underline; }
    </style>
</head>
<body>
    <div class="admin-section">
        <div style="margin-bottom: 18px; text-align:left;">
            <a href="admin_dashboard.php" class="admin-btn" style="display:inline-block;">&larr; Back to Dashboard</a>
        </div>
        <h2>Import Questions from API</h2>
        <?php if ($import_msg): ?><div class="admin-msg"><?php echo htmlspecialchars($import_msg); ?></div><?php endif; ?>
        <form method="post" style="margin-bottom:24px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <label for="import_topic">Topic:</label>
            <select name="import_topic" id="import_topic" required style="padding:8px 12px; border-radius:6px; border:1px solid #fda085; font-size:1.08em;">
                <option value="">Select Topic</option>
                <?php foreach ($topics as $t): ?>
                    <option value="<?php echo htmlspecialchars($t['name']); ?>"><?php echo htmlspecialchars(ucfirst($t['name'])); ?></option>
                <?php endforeach; ?>
            </select>
            <label for="import_difficulty">Difficulty:</label>
            <select name="import_difficulty" id="import_difficulty" required style="padding:8px 12px; border-radius:6px; border:1px solid #fda085; font-size:1.08em;">
                <option value="">Select Difficulty</option>
                <option value="easy">Easy</option>
                <option value="medium">Medium</option>
                <option value="hard">Hard</option>
            </select>
            <label for="import_amount">Amount:</label>
            <input type="number" name="import_amount" id="import_amount" min="1" max="50" value="5" required style="padding:8px 12px; border-radius:6px; border:1px solid #fda085; font-size:1.08em; width:80px;">
            <button class="admin-btn" name="import_api">Import from API</button>
        </form>
        <h2>Question Management</h2>
        <?php if ($add_msg): ?><div class="admin-msg"><?php echo htmlspecialchars($add_msg); ?></div><?php endif; ?>
        <form method="get" class="filter-bar">
            <label>Filter by Topic:</label>
            <select name="filter_topic">
                <option value="">All</option>
                <?php foreach ($topics as $t): ?>
                    <option value="<?php echo htmlspecialchars($t['name']); ?>" <?php if ($filter_topic == $t['name']) echo 'selected'; ?>><?php echo htmlspecialchars(ucfirst($t['name'])); ?></option>
                <?php endforeach; ?>
            </select>
            <label>Difficulty:</label>
            <select name="filter_difficulty">
                <option value="">All</option>
                <option value="easy" <?php if ($filter_difficulty == 'easy') echo 'selected'; ?>>Easy</option>
                <option value="medium" <?php if ($filter_difficulty == 'medium') echo 'selected'; ?>>Medium</option>
                <option value="hard" <?php if ($filter_difficulty == 'hard') echo 'selected'; ?>>Hard</option>
            </select>
            <button class="admin-btn" type="submit">Apply</button>
        </form>
        <form method="post" class="admin-form">
            <label>Question:</label><input type="text" name="question" required style="grid-column: span 9;">
            <label>A:</label><input type="text" name="option_a" required>
            <label>B:</label><input type="text" name="option_b" required>
            <label>C:</label><input type="text" name="option_c" required>
            <label>D:</label><input type="text" name="option_d" required>
            <label>Answer:</label>
            <select name="answer" required>
                <option value="a">A</option>
                <option value="b">B</option>
                <option value="c">C</option>
                <option value="d">D</option>
            </select>
            <label>Topic:</label><input type="text" name="topic" required>
            <label>Difficulty:</label>
            <select name="difficulty" required>
                <option value="easy">Easy</option>
                <option value="medium">Medium</option>
                <option value="hard">Hard</option>
            </select>
            <button class="admin-btn" name="add_question">Add Question</button>
        </form>
        <table class="admin-table">
            <tr><th>ID</th><th>Question</th><th>A</th><th>B</th><th>C</th><th>D</th><th>Answer</th><th>Topic</th><th>Difficulty</th><th>Preview</th><th>Action</th></tr>
            <?php foreach ($questions as $q): ?>
            <tr>
                <td><?php echo $q['id']; ?></td>
                <td><?php $words = explode(' ', $q['question']); echo htmlspecialchars(implode(' ', array_slice($words, 0, 4))) . (count($words) > 4 ? '...' : ''); ?></td>
                <td><?php echo htmlspecialchars($q['option_a']); ?></td>
                <td><?php echo htmlspecialchars($q['option_b']); ?></td>
                <td><?php echo htmlspecialchars($q['option_c']); ?></td>
                <td><?php echo htmlspecialchars($q['option_d']); ?></td>
                <td><?php echo strtoupper($q['answer']); ?></td>
                <td><?php echo htmlspecialchars($q['topic']); ?></td>
                <td><?php echo htmlspecialchars($q['difficulty']); ?></td>
                <td><span class="preview-eye" onclick="showPreviewQ(<?php echo $q['id']; ?>)">&#128065;</span></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                        <button class="admin-btn delete" name="delete_question">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div style="text-align:center; margin-bottom:24px;">
            <?php if ($page > 1): ?>
                <a href="?qpage=<?php echo $page-1; ?>&filter_topic=<?php echo urlencode($filter_topic); ?>&filter_difficulty=<?php echo urlencode($filter_difficulty); ?>" class="admin-btn pagelink" style="margin-right:8px;">Previous</a>
            <?php endif; ?>
            <?php
            $max_links = 5;
            $start = max(1, $page - intval($max_links/2));
            $end = min($total_pages, $start + $max_links - 1);
            if ($end - $start + 1 < $max_links) $start = max(1, $end - $max_links + 1);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="?qpage=<?php echo $i; ?>&filter_topic=<?php echo urlencode($filter_topic); ?>&filter_difficulty=<?php echo urlencode($filter_difficulty); ?>"
                   class="pagelink"
                   style="margin:0 3px; text-decoration:<?php echo ($i == $page ? 'underline' : 'none'); ?>; font-weight:<?php echo ($i == $page ? 'bold' : 'normal'); ?>; color:#f76b1c;">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            <span style="font-weight:600; color:#f76b1c; margin:0 8px;">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            <?php if ($page < $total_pages): ?>
                <a href="?qpage=<?php echo $page+1; ?>&filter_topic=<?php echo urlencode($filter_topic); ?>&filter_difficulty=<?php echo urlencode($filter_difficulty); ?>" class="admin-btn pagelink" style="margin-left:8px;">Next</a>
            <?php endif; ?>
        </div>
        <div class="modal-bg" id="previewModalQ">
            <div class="modal-content" id="modalContentQ">
                <span class="modal-close" onclick="closePreviewQ()">&times;</span>
                <div id="modalBodyQ">Loading...</div>
            </div>
        </div>
        <script>
        function showPreviewQ(qid) {
            fetch('question_preview.php?id=' + qid)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('modalBodyQ').innerHTML = html;
                    document.getElementById('previewModalQ').classList.add('active');
                });
        }
        function closePreviewQ() {
            document.getElementById('previewModalQ').classList.remove('active');
        }
        </script>
    </div>
</body>
</html> 