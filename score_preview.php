<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Not authorized');
}
if (!isset($_GET['id'])) {
    exit('No score ID');
}
require_once 'db.php';
$score_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT details FROM scores WHERE id = ? AND user_id = ?');
$stmt->execute([$score_id, $user_id]);
$row = $stmt->fetch();
if (!$row || empty($row['details'])) {
    exit('No details found.');
}
$details = json_decode($row['details'], true);
?>
<h2 style="color:#f76b1c; margin-bottom:18px;">Quiz Preview</h2>
<div style="text-align:left;">
<?php foreach ($details as $i => $q): ?>
    <div style="margin-bottom: 22px; padding: 14px 14px 8px 14px; background: #fff8f2; border-radius: 10px; box-shadow: 0 2px 8px #fda08522;">
        <div style="font-weight:600; color:#f76b1c; margin-bottom:8px;">Q<?php echo ($i+1); ?>. <?php echo htmlspecialchars($q['question']); ?></div>
        <div style="margin-left: 12px;">
            <?php foreach ($q['options'] as $key => $option):
                $is_correct = ($key === $q['correct']);
                $is_user = ($q['user'] === $key);
                $option_style = '';
                if ($is_correct) {
                    $option_style = 'background:#d4f8e5;color:#218838;font-weight:600;';
                }
                if ($is_user && !$is_correct) {
                    $option_style = 'background:#ffe0e0;color:#c0392b;font-weight:600;';
                }
                if ($is_user && $is_correct) {
                    $option_style = 'background:#d4f8e5;color:#218838;font-weight:600; border:2px solid #27ae60;';
                }
            ?>
            <div style="display:inline-block; min-width: 60px; margin: 4px 0; padding: 6px 16px; border-radius: 6px; <?php echo $option_style; ?>">
                <?php echo htmlspecialchars($option); ?>
                <?php if ($is_user): ?>
                    <span style="font-size:0.95em; opacity:0.7;">(Your answer)</span>
                <?php endif; ?>
                <?php if ($is_correct): ?>
                    <span style="font-size:0.95em; opacity:0.7;">(Correct)</span>
                <?php endif; ?>
            </div><br>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
</div> 