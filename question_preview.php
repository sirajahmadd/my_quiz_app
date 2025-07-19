<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    exit('Not authorized');
}
if (!isset($_GET['id'])) {
    exit('No question ID');
}
require_once 'db.php';
$qid = intval($_GET['id']);
$stmt = $pdo->prepare('SELECT * FROM questions WHERE id = ?');
$stmt->execute([$qid]);
$q = $stmt->fetch();
if (!$q) { exit('No question found.'); }
?>
<h2 style="color:#f76b1c; margin-bottom:18px;">Question Preview</h2>
<div style="text-align:left;">
    <div style="margin-bottom: 18px; font-weight:600; color:#f76b1c;">Q: <?php echo htmlspecialchars($q['question']); ?></div>
    <div style="margin-left: 12px;">
        <div style="margin-bottom:8px;"><b>A:</b> <?php echo htmlspecialchars($q['option_a']); ?></div>
        <div style="margin-bottom:8px;"><b>B:</b> <?php echo htmlspecialchars($q['option_b']); ?></div>
        <div style="margin-bottom:8px;"><b>C:</b> <?php echo htmlspecialchars($q['option_c']); ?></div>
        <div style="margin-bottom:8px;"><b>D:</b> <?php echo htmlspecialchars($q['option_d']); ?></div>
        <div style="margin-top:12px;"><b>Correct Answer:</b> <?php echo strtoupper($q['answer']); ?></div>
        <div><b>Topic:</b> <?php echo htmlspecialchars($q['topic']); ?></div>
        <div><b>Difficulty:</b> <?php echo htmlspecialchars($q['difficulty']); ?></div>
    </div>
</div> 